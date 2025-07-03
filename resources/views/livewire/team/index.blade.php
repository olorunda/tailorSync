<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-96">
            <div class="flex flex-col items-center gap-2">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-orange-600"></div>
                <span class="text-orange-600 text-lg">Loading...</span>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {

        return [
            'team_members' => Auth::user()->allTeamMembers()
                ->when($this->search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="w-full">
    @php
        // Get subscription plan details
        $user = auth()->user();
        $businessDetail = $user->businessDetail;
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = \App\Services\SubscriptionService::getPlan($planKey);
        $maxTeamMembers = $plan['features']['max_team_members'] ?? 1;
        $currentTeamCount = \App\Models\User::where('parent_id', $user->id)->count();
        $isUnlimited = $maxTeamMembers === 'unlimited';
        $isNearLimit = !$isUnlimited && $currentTeamCount >= ($maxTeamMembers * 0.8); // 80% of limit
        $isAtLimit = !$isUnlimited && $currentTeamCount >= $maxTeamMembers;
    @endphp

    @if($isAtLimit)
        <x-subscription-limit-notice
            feature="team member limit"
            message="You have reached the maximum number of team members ({{ $maxTeamMembers }}) allowed for your {{ ucfirst($planKey) }} plan."
        />
    @elseif($isNearLimit)
        <x-subscription-limit-notice
            feature="team member limit"
            message="You are approaching the maximum number of team members allowed for your {{ ucfirst($planKey) }} plan. You have used {{ $currentTeamCount }} out of {{ $maxTeamMembers }} available team member slots."
        />
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Team</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage your team members and their roles</p>
        </div>
        @if(auth()->user()->hasPermission('create_team'))
            <a href="{{ route('team.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Team Member
            </a>
        @endif
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5" placeholder="Search team members...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="responsive-table min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Joined</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($team_members as $member)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Name">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 dark:text-purple-500 font-medium text-sm">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $member->role }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Email">
                                <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $member->email }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $member->phone ?? 'No phone' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Role">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                    {{ $member->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Joined">
                                <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $member->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" data-label="Actions">
                                <a href="{{ route('team.show', $member) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 mr-3">View</a>
                                <a href="{{ route('team.edit', $member) }}" class="text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">No team members found</p>
                                    @if(auth()->user()->hasPermission('create_team'))
                                        <a href="{{ route('team.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Add Your First Team Member
                                        </a>
                                    @else
                                        <p class="text-zinc-500 dark:text-zinc-400">You don't have permission to add team members.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $team_members->links() }}
        </div>
    </div>
</div>
