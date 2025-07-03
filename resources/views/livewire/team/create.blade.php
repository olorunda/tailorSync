<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $currentPlan = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $phone = '';
    public ?string $position = '';
    public string $role = 'team_member';
    public $photo = null;
    public ?string $notes = '';
    public $roles = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // Fetch roles from the database
        $this->roles = Role::all();
        $currentUser = Auth::user();
        $businessDetail = $currentUser->businessDetail;
        $this->currentPlan = $businessDetail->subscription_plan ?? 'free';
    }

    public function save(): void
    {
        // Get valid role IDs from the database
        $validRoleIds = $this->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();

        // Include string role values for backward compatibility
        $validRoleValues = array_merge($validRoleIds, ['admin', 'manager', 'team_member', 'tailor', 'designer']);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:' . implode(',', $validRoleValues)],
            'photo' => ['nullable', 'image', 'max:1024'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check team member limit based on subscription plan
        $currentUser = Auth::user();
        $businessDetail = $currentUser->businessDetail;
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = \App\Services\SubscriptionService::getPlan($planKey);

        // Get the max team members allowed for the subscription plan
        $maxTeamMembers = $plan['features']['max_team_members'] ?? 1;

        // Get the current team member count
        $currentTeamCount = User::where('parent_id', $currentUser->id)->count();

        // Check if the user has reached the team member limit
        if ($maxTeamMembers !== 'unlimited' && $currentTeamCount >= $maxTeamMembers) {
            session()->flash('subscription_limit_reached', "You have reached the maximum number of team members ({$maxTeamMembers}) allowed for your subscription plan. Please upgrade your plan to add more team members.");
            return;
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('profile-photos', 'public');
        }

        // Prepare user data
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'phone' => $this->phone,
            'position' => $this->position,
            'profile_photo_path' => $photoPath,
            'notes' => $this->notes,
            'parent_id' => Auth::id(), // Set the parent_id to the currently authenticated user
        ];

        // Check if the role is a numeric ID (from the database) or a string (legacy)
        if (is_numeric($this->role)) {
            $userData['role_id'] = $this->role;
            // Keep the string role for backward compatibility
            $roleName = $this->roles->firstWhere('id', (int)$this->role)->name ?? null;
            $userData['role'] = $roleName;
        } else {
            // It's a string role (legacy)
            $userData['role'] = $this->role;
            // Try to find a matching role in the database
            $role = $this->roles->firstWhere('name', $this->role);
            $userData['role_id'] = $role ? $role->id : null;
        }

        $user = User::create($userData);

        $this->redirect(route('team.index'));
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Add Team Member</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Create a new account for a team member</p>
    </div>
    @if (session('subscription_limit_reached'))
        <x-subscription-limit-notice
            feature="{{ session('subscription_feature') }}"
            plan="{{ session('subscription_plan',  $currentPlan) }}"
            message="{{ session('error') }}"
        />
    @elseif (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                    <input wire:model="name" type="text" id="name" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                    <input wire:model="email" type="email" id="email" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
                    <input wire:model="password" type="password" id="password" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                    @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Confirm Password</label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Phone (Optional)</label>
                    <input wire:model="phone" type="text" id="phone" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="position" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Position (Optional)</label>
                    <input wire:model="position" type="text" id="position" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('position') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Role</label>
                    <select wire:model="role" id="role" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5" required>
                        <!-- Database roles -->
                        @foreach($roles as $dbRole)
                            <option value="{{ $dbRole->id }}">{{ $dbRole->name }}</option>
                        @endforeach

                        <!-- Legacy roles for backward compatibility -->
{{--                        <option value="team_member">Team Member</option>--}}
{{--                        <option value="tailor">Tailor</option>--}}
{{--                        <option value="designer">Designer</option>--}}
{{--                        <option value="manager">Manager</option>--}}
{{--                        <option value="admin">Administrator</option>--}}
                    </select>
                    @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="photo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Profile Photo (Optional)</label>
                    <input wire:model="photo" type="file" id="photo" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('photo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($photo)
                        <div class="mt-2">
                            <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-full">
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes (Optional)</label>
                    <textarea wire:model="notes" id="notes" rows="3" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5"></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('team.index') }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Add Team Member
                </button>
            </div>
        </form>
    </div>
</div>
