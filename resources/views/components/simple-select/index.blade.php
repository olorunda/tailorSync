@props([
    'options' => [],
    'placeholder' => 'Select an option',
    'optionLabel' => 'name',
    'optionValue' => 'id',
    'noResultsText' => 'No results found',
    'searchable' => true,
    'clearable' => true,
    'multiple' => false,
    'disabled' => false,
    'maxSelection' => null,
    'maxSelectionMessage' => 'You can only select {max} items',
    'maxHeight' => '300px',
    'position' => 'auto',
    'required' => false,
])

@php
    $id = $attributes->get('id', 'simple-select-' . uniqid());
    $name = $attributes->get('name', $id);
    $model = $attributes->whereStartsWith('wire:model')->first();
    $modelKey = $model ? substr($model, strpos($model, ':') ) : null;
    $isWired = $model !== null;
    $isLivewire = $isWired && str_contains($model, '.live');
    $isDeferred = $isWired && str_contains($model, '.defer');
    $isLazy = $isWired && str_contains($model, '.lazy');
@endphp

<div
    x-data="simpleSelect({
        id: '{{ $id }}',
        name: '{{ $name }}',
        options: {{ json_encode($options) }},
        optionLabel: '{{ $optionLabel }}',
        optionValue: '{{ $optionValue }}',
        placeholder: '{{ $placeholder }}',
        noResultsText: '{{ $noResultsText }}',
        searchable: {{ $searchable ? 'true' : 'false' }},
        clearable: {{ $clearable ? 'true' : 'false' }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        maxSelection: {{ $maxSelection ? $maxSelection : 'null' }},
        maxSelectionMessage: '{{ $maxSelectionMessage }}',
        maxHeight: '{{ $maxHeight }}',
        position: '{{ $position }}',
        required: {{ $required ? 'true' : 'false' }},
        @if($isWired)
        wireModel: '{{ $modelKey }}',
        isLivewire: {{ $isLivewire ? 'true' : 'false' }},
        isDeferred: {{ $isDeferred ? 'true' : 'false' }},
        isLazy: {{ $isLazy ? 'true' : 'false' }},
        @endif
    })"
    x-init="init()"
    @if($isWired)
    x-on:wire-model-updated.window="onWireModelUpdated($event)"
    @endif
    class="simple-select-container"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    <div
        class="simple-select-control"
        :class="{ 'simple-select-disabled': disabled, 'simple-select-focused': open }"
        @click="toggleDropdown"
    >
        <div class="simple-select-value-container">
            <template x-if="!hasSelection && placeholder">
                <div class="simple-select-placeholder" x-text="placeholder"></div>
            </template>
            <template x-if="!multiple && selectedOption">
                <div class="simple-select-single-value" x-text="getOptionLabel(selectedOption)"></div>
            </template>
            <template x-if="multiple">
                <div class="simple-select-multi-value-wrapper">
                    <template x-for="(option, index) in selectedOptions" :key="index">
                        <div class="simple-select-multi-value">
                            <div class="simple-select-multi-value-label" x-text="getOptionLabel(option)"></div>
                            <div class="simple-select-multi-value-remove" @click.stop="removeOption(option)">×</div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
        <div class="simple-select-indicators">
            <template x-if="clearable && hasSelection">
                <div class="simple-select-clear-indicator" @click.stop="clearSelection">×</div>
            </template>
            <div class="simple-select-dropdown-indicator">
                <svg height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false" class="simple-select-dropdown-arrow">
                    <path d="M4.516 7.548c0.436-0.446 1.043-0.481 1.576 0l3.908 3.747 3.908-3.747c0.533-0.481 1.141-0.446 1.574 0 0.436 0.445 0.408 1.197 0 1.615-0.406 0.418-4.695 4.502-4.695 4.502-0.217 0.223-0.502 0.335-0.787 0.335s-0.57-0.112-0.789-0.335c0 0-4.287-4.084-4.695-4.502s-0.436-1.17 0-1.615z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div
        x-show="open"
        x-transition:enter="simple-select-dropdown-enter"
        x-transition:enter-start="simple-select-dropdown-enter-start"
        x-transition:enter-end="simple-select-dropdown-enter-end"
        x-transition:leave="simple-select-dropdown-leave"
        x-transition:leave-start="simple-select-dropdown-leave-start"
        x-transition:leave-end="simple-select-dropdown-leave-end"
        class="simple-select-dropdown"
        :class="dropdownPosition"
        :style="{ 'max-height': maxHeight }"
        @click.outside="open = false"
    >
        <template x-if="searchable">
            <div class="simple-select-search">
                <input
                    type="text"
                    class="simple-select-search-input"
                    x-model="search"
                    @click.stop
                    @keydown.enter.prevent="selectOption(filteredOptions[highlightedIndex])"
                    @keydown.arrow-up.prevent="navigateOptions('up')"
                    @keydown.arrow-down.prevent="navigateOptions('down')"
                    @keydown.escape.stop.prevent="open = false"
                    placeholder="Search..."
                />
            </div>
        </template>
        <div class="simple-select-menu">
            <template x-if="filteredOptions.length === 0">
                <div class="simple-select-no-results" x-text="noResultsText"></div>
            </template>
            <template x-for="(option, index) in filteredOptions" :key="index">
                <div
                    class="simple-select-option"
                    :class="{
                        'simple-select-option-selected': isSelected(option),
                        'simple-select-option-highlighted': highlightedIndex === index
                    }"
                    @click.stop="selectOption(option)"
                    @mouseenter="highlightedIndex = index"
                    x-text="getOptionLabel(option)"
                ></div>
            </template>
        </div>
    </div>

    <template x-if="multiple">
        <select :name="name + '[]'" :id="id" multiple style="display: none;" :required="required">
            <template x-for="option in selectedOptions" :key="getOptionValue(option)">
                <option :value="getOptionValue(option)" selected></option>
            </template>
        </select>
    </template>
    <template x-if="!multiple">
        <select :name="name" :id="id" style="display: none;" :required="required">
            <option x-bind:value="selectedValue" selected></option>
        </select>
    </template>
</div>
