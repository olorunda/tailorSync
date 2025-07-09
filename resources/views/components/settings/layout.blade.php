<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            @if(auth()->user()->parent_id === null)
                <flux:navlist.item :href="route('settings.business')" wire:navigate>{{ __('Business') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.business-profile')" wire:navigate>{{ __('Business Profile') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.public-booking')" wire:navigate>{{ __('Public Booking') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.subscription-history')" wire:navigate>{{ __('Subscription History') }}</flux:navlist.item>
                @if(auth()->user()->hasPermission('manage_store'))
                    <flux:navlist.item :href="route('settings.store')" wire:navigate>{{ __('Store') }}</flux:navlist.item>
                @endif
            @endif
            @if(auth()->user()->hasPermission('manage_measurements'))
                <flux:navlist.item :href="route('settings.measurements')" wire:navigate>{{ __('Measurements') }}</flux:navlist.item>
            @endif
            @if(auth()->user()->hasRole('admin'))
                <flux:navlist.item :href="route('settings.roles')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.permissions')" wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
            @endif
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
