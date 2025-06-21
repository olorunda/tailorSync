<?php

use App\Models\MeasurementType;

?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Measurement Settings')" :subheading="__('Create and manage custom measurement types')">
        <div class="my-6 w-full space-y-8">
            <!-- Measurement Types Management Section -->
            <div>
                <flux:heading size="lg">{{ __('Measurement Types') }}</flux:heading>
                <flux:separator variant="subtle" class="mb-4" />

                <!-- Create/Edit Measurement Type Form -->
                <form wire:submit.prevent="{{ $isEditing ? 'updateMeasurementType' : 'createMeasurementType' }}" class="mb-6 space-y-4">
                    <flux:heading size="sm">{{ $isEditing ? __('Edit Measurement Type') : __('Create New Measurement Type') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:input wire:model="newMeasurementType.name" :label="__('Name')" type="text" required />
                            @error('newMeasurementType.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <flux:input wire:model="newMeasurementType.unit" :label="__('Unit')" type="text" required />
                            @error('newMeasurementType.unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <flux:textarea wire:model="newMeasurementType.description" :label="__('Description')" rows="3" />
                        @error('newMeasurementType.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <flux:checkbox wire:model="newMeasurementType.is_active" :label="__('Active')" />
                    </div>

                    <div class="flex space-x-2">
                        <flux:button variant="primary" type="submit">
                            {{ $isEditing ? __('Update Measurement Type') : __('Create Measurement Type') }}
                        </flux:button>

                        @if($isEditing)
                            <flux:button wire:click="resetForm" variant="secondary" type="button">
                                {{ __('Cancel') }}
                            </flux:button>
                        @endif
                    </div>
                </form>

                <!-- Measurement Types List -->
                <div class="mt-8">
                    <flux:heading size="sm">{{ __('Your Measurement Types') }}</flux:heading>

                    @if($measurementTypes->count() > 0)
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Name') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Unit') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Description') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Status') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($measurementTypes as $type)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $type->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $type->unit }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $type->description }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $type->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ $type->is_active ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button wire:click="toggleActive({{ $type->id }})" class="text-orange-600 dark:text-orange-500 hover:text-orange-900 dark:hover:text-orange-400">
                                                        {{ $type->is_active ? __('Deactivate') : __('Activate') }}
                                                    </button>
                                                    <button wire:click="editMeasurementType({{ $type->id }})" class="text-blue-600 dark:text-blue-500 hover:text-blue-900 dark:hover:text-blue-400">
                                                        {{ __('Edit') }}
                                                    </button>
                                                    <button wire:click="deleteMeasurementType({{ $type->id }})" class="text-red-600 dark:text-red-500 hover:text-red-900 dark:hover:text-red-400" onclick="return confirm('{{ __('Are you sure you want to delete this measurement type?') }}')">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No measurement types found. Create your first one above.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
