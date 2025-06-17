<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public User $teamMember;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $phone = '';
    public ?string $position = '';
    public string $role = '';
    public $photo = null;
    public $newPhoto = null;
    public ?string $notes = '';
    public $roles = [];

    public function mount(User $teamMember): void
    {
        $this->teamMember = $teamMember;
        $this->name = $teamMember->name;
        $this->email = $teamMember->email;
        $this->phone = $teamMember->phone ?? '';
        $this->position = $teamMember->position ?? '';
        $this->role = $teamMember->role_id ? (string)$teamMember->role_id : ($teamMember->role ?? 'team_member');
        $this->photo = $teamMember->profile_photo_path;
        $this->notes = $teamMember->notes ?? '';

        // Fetch roles from the database
        $this->roles = Role::all();
    }

    public function save(): void
    {
        // Get valid role IDs from the database
        $validRoleIds = $this->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();

        // Include string role values for backward compatibility
        $validRoleValues = array_merge($validRoleIds, ['admin', 'manager', 'team_member', 'tailor', 'designer']);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->teamMember->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:' . implode(',', $validRoleValues)],
            'newPhoto' => ['nullable', 'image', 'max:1024'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Only validate password if it's being updated
        if ($this->password) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        if ($this->newPhoto) {
            // Delete old photo if exists
            if ($this->photo && Storage::disk('public')->exists($this->photo)) {
                Storage::disk('public')->delete($this->photo);
            }

            $photoPath = $this->newPhoto->store('profile-photos', 'public');
            $this->teamMember->profile_photo_path = $photoPath;
        }

        $this->teamMember->name = $this->name;
        $this->teamMember->email = $this->email;

        if ($this->password) {
            $this->teamMember->password = Hash::make($this->password);
        }

        $this->teamMember->phone = $this->phone;
        $this->teamMember->position = $this->position;

        // Check if the role is a numeric ID (from the database) or a string (legacy)
        if (is_numeric($this->role)) {
            $this->teamMember->role_id = $this->role;
            // Keep the string role for backward compatibility
            $roleName = $this->roles->firstWhere('id', (int)$this->role)->name ?? null;
            $this->teamMember->role = $roleName;
        } else {
            // It's a string role (legacy)
            $this->teamMember->role = $this->role;
            // Try to find a matching role in the database
            $role = $this->roles->firstWhere('name', $this->role);
            $this->teamMember->role_id = $role ? $role->id : null;
        }

        $this->teamMember->notes = $this->notes;
        $this->teamMember->save();

        $this->redirect(route('team.show', $this->teamMember));
    }

    public function deletePhoto(): void
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            Storage::disk('public')->delete($this->photo);
        }

        $this->teamMember->profile_photo_path = null;
        $this->teamMember->save();
        $this->photo = null;
    }
}; ?>

<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Edit Team Member</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update team member information</p>
    </div>

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
                    <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Password (leave blank to keep current)</label>
                    <input wire:model="password" type="password" id="password" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Confirm New Password</label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
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
                            <option value="{{ $dbRole->id }}">{{ ucfirst($dbRole->name) }}</option>
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
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Profile Photo</label>

                    @if ($photo && !$newPhoto)
                        <div class="mb-3 flex items-center">
                            <img src="{{ Storage::url($photo) }}" alt="{{ $teamMember->name }}" class="h-16 w-16 rounded-full object-cover mr-3">
                            <button type="button" wire:click="deletePhoto" wire:confirm="Are you sure you want to remove this photo?" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Remove Photo
                            </button>
                        </div>
                    @endif

                    <input wire:model="newPhoto" type="file" id="newPhoto" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                    @error('newPhoto') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                    @if ($newPhoto)
                        <div class="mt-2">
                            <img src="{{ $newPhoto->temporaryUrl() }}" class="h-16 w-16 object-cover rounded-full">
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
                <a href="{{ route('team.show', $teamMember) }}" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
