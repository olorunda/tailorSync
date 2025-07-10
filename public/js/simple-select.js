// Add a global helper function to debug SimpleSelect components
window.debugSimpleSelect = function(id = null) {
    window.dispatchEvent(new CustomEvent('simple-select-debug', {
        detail: {
            id: id,
            all: id === null
        }
    }));
};

document.addEventListener('alpine:init', () => {
    Alpine.data('simpleSelect', (config) => ({
        id: config.id,
        name: config.name,
        options: config.options || [],
        optionLabel: config.optionLabel || 'name',
        optionValue: config.optionValue || 'id',
        placeholder: config.placeholder || 'Select an option',
        noResultsText: config.noResultsText || 'No results found',
        searchable: config.searchable !== undefined ? config.searchable : true,
        clearable: config.clearable !== undefined ? config.clearable : true,
        multiple: config.multiple !== undefined ? config.multiple : false,
        disabled: config.disabled !== undefined ? config.disabled : false,
        maxSelection: config.maxSelection || null,
        maxSelectionMessage: config.maxSelectionMessage || 'You can only select {max} items',
        maxHeight: config.maxHeight || '300px',
        position: config.position || 'auto',
        required: config.required !== undefined ? config.required : false,
        wireModel: config.wireModel || null,
        isLivewire: config.isLivewire || false,
        isDeferred: config.isDeferred || false,
        isLazy: config.isLazy || false,

        open: false,
        search: '',
        highlightedIndex: 0,
        selectedValue: null,
        selectedOption: null,
        selectedOptions: [],
        dropdownPosition: 'simple-select-dropdown-bottom',

        init() {

            console.log('SimpleSelect: Initializing', {
                id: this.id,
                name: this.name,
                wireModel: this.wireModel,
                options: this.options
            });

            this.initializeOptions();
            this.initializeWireModel();
            this.calculateDropdownPosition();

            window.addEventListener('resize', () => {
                this.calculateDropdownPosition();
            });

            // Add a global event listener for debugging
            window.addEventListener('simple-select-debug', (event) => {
                if (event.detail.id === this.id || event.detail.all) {
                    console.log('SimpleSelect: Debug info', {
                        id: this.id,
                        name: this.name,
                        wireModel: this.wireModel,
                        selectedValue: this.selectedValue,
                        selectedOption: this.selectedOption,
                        selectedOptions: this.selectedOptions,
                        options: this.options
                    });
                }
            });
        },

        initializeOptions() {
            // Convert options from string format if needed
            if (typeof this.options === 'string') {
                try {
                    this.options = JSON.parse(this.options);
                } catch (e) {
                    console.error('Error parsing options:', e);
                    this.options = [];
                }
            }

            // Initialize selected options
            if (this.multiple) {
                this.selectedOptions = [];
            } else {
                this.selectedOption = null;
                this.selectedValue = null;
            }
        },

        initializeWireModel() {
            if (this.wireModel) {
                // For Livewire integration
                this.$nextTick(() => {
                    // Initialize the component with the current value from Livewire
                    if (this.$wire.get(this.wireModel) !== undefined) {
                        this.setValueFromWire(this.$wire.get(this.wireModel));
                    }

                    // Watch for changes to the selected value and update Livewire
                    this.$watch('selectedValue', (value) => {
                        if (value !== undefined) {
                            // Always update Livewire when the value changes
                            this.$wire.set(this.wireModel, value);

                            // Dispatch a custom event to notify other components
                            this.$dispatch('simple-select-updated', {
                                id: this.id,
                                name: this.name,
                                value: value
                            });
                        }
                    });

                    // Watch for changes to the selected options (for multiple select)
                    this.$watch('selectedOptions', (value) => {
                        if (this.multiple && value.length > 0) {
                            const values = value.map(option => this.getOptionValue(option));
                            // Always update Livewire when the values change
                            this.$wire.set(this.wireModel, values);

                            // Dispatch a custom event to notify other components
                            this.$dispatch('simple-select-updated', {
                                id: this.id,
                                name: this.name,
                                value: values
                            });
                        }
                    });
                });
            }
        },

        onWireModelUpdated(event) {
            // Check if this event is for our model
            // Handle both direct matches and nested property paths
            if (event.detail.name === this.wireModel ||
                event.detail.name.endsWith('.' + this.wireModel) ||
                this.wireModel.endsWith('.' + event.detail.name)) {

                const value = event.detail.value;
                this.setValueFromWire(value);

                // Log for debugging
                console.log('SimpleSelect: Updated from Livewire', {
                    id: this.id,
                    wireModel: this.wireModel,
                    eventName: event.detail.name,
                    value: value
                });
            }
        },

        setValueFromWire(value) {
            // Handle null/undefined values
            if (value === null || value === undefined) {
                if (this.multiple) {
                    this.selectedOptions = [];
                } else {
                    this.selectedValue = null;
                    this.selectedOption = null;
                }
                return;
            }

            if (this.multiple) {
                // Handle array values for multiple select
                if (Array.isArray(value)) {
                    // Convert all values to strings for consistent comparison
                    const valueStrings = value.map(v => String(v));

                    this.selectedOptions = this.options.filter(option => {
                        const optionValue = String(this.getOptionValue(option));
                        return valueStrings.includes(optionValue);
                    });

                    // Log if we couldn't find all options
                    if (this.selectedOptions.length !== value.length) {
                        console.warn('SimpleSelect: Not all values could be matched to options', {
                            id: this.id,
                            values: value,
                            matchedOptions: this.selectedOptions
                        });
                    }
                } else if (value !== '') {
                    // Handle single value for multiple select (convert to array)
                    const optionValue = String(value);
                    const option = this.options.find(opt => String(this.getOptionValue(opt)) === optionValue);

                    if (option) {
                        this.selectedOptions = [option];
                    } else {
                        console.warn('SimpleSelect: Value could not be matched to an option', {
                            id: this.id,
                            value: value
                        });
                        this.selectedOptions = [];
                    }
                } else {
                    // Empty value
                    this.selectedOptions = [];
                }
            } else {
                // Handle single select
                if (value === '') {
                    this.selectedValue = null;
                    this.selectedOption = null;
                } else {
                    // Convert to string for consistent comparison
                    this.selectedValue = value;
                    const valueString = String(value);

                    this.selectedOption = this.options.find(option =>
                        String(this.getOptionValue(option)) === valueString
                    );

                    // Log if we couldn't find the option
                    if (!this.selectedOption) {
                        console.warn('SimpleSelect: Value could not be matched to an option', {
                            id: this.id,
                            value: value,
                            options: this.options.map(o => this.getOptionValue(o))
                        });
                    }
                }
            }
        },

        calculateDropdownPosition() {
            if (this.position !== 'auto') {
                this.dropdownPosition = `simple-select-dropdown-${this.position}`;
                return;
            }

            this.$nextTick(() => {
                const rect = this.$el.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;

                if (spaceBelow < 300 && spaceAbove > spaceBelow) {
                    this.dropdownPosition = 'simple-select-dropdown-top';
                } else {
                    this.dropdownPosition = 'simple-select-dropdown-bottom';
                }
            });
        },

        toggleDropdown() {
            if (this.disabled) return;
            this.open = !this.open;

            if (this.open) {
                this.calculateDropdownPosition();
                this.search = '';
                this.highlightedIndex = 0;

                this.$nextTick(() => {
                    const searchInput = this.$el.querySelector('.simple-select-search-input');
                    if (searchInput) {
                        searchInput.focus();
                    }
                });
            }
        },

        get filteredOptions() {
            if (!this.search.trim()) {
                return this.options;
            }

            const searchLower = this.search.toLowerCase();
            return this.options.filter(option => {
                const label = this.getOptionLabel(option).toLowerCase();
                return label.includes(searchLower);
            });
        },

        getOptionLabel(option) {
            if (!option) return '';

            if (typeof option === 'object') {
                return option[this.optionLabel] || '';
            }

            return option.toString();
        },

        getOptionValue(option) {
            if (!option) return '';

            if (typeof option === 'object') {
                return option[this.optionValue] || '';
            }

            return option.toString();
        },

        selectOption(option) {
            if (!option) return;

            if (this.multiple) {
                const isSelected = this.isSelected(option);

                if (isSelected) {
                    this.removeOption(option);
                } else {
                    if (this.maxSelection !== null && this.selectedOptions.length >= this.maxSelection) {
                        alert(this.maxSelectionMessage.replace('{max}', this.maxSelection));
                        return;
                    }

                    this.selectedOptions.push(option);
                }

                // Directly update Livewire if wired
                if (this.wireModel) {
                    const values = this.selectedOptions.map(opt => this.getOptionValue(opt));
                    this.$wire.set(this.wireModel, values);
                }
            } else {
                this.selectedOption = option;
                this.selectedValue = this.getOptionValue(option);

                // Directly update Livewire if wired
                if (this.wireModel) {
                    this.$wire.set(this.wireModel, this.selectedValue);
                }

                this.open = false;
            }
        },

        removeOption(option) {
            const value = this.getOptionValue(option);
            this.selectedOptions = this.selectedOptions.filter(
                selected => this.getOptionValue(selected) !== value
            );

            // Directly update Livewire if wired
            if (this.wireModel) {
                const values = this.selectedOptions.map(opt => this.getOptionValue(opt));
                this.$wire.set(this.wireModel, values);
            }
        },

        clearSelection() {
            if (this.multiple) {
                this.selectedOptions = [];

                // Directly update Livewire if wired
                if (this.wireModel) {
                    this.$wire.set(this.wireModel, []);
                }
            } else {
                this.selectedOption = null;
                this.selectedValue = null;

                // Directly update Livewire if wired
                if (this.wireModel) {
                    this.$wire.set(this.wireModel, null);
                }
            }
        },

        isSelected(option) {
            const value = this.getOptionValue(option);

            if (this.multiple) {
                return this.selectedOptions.some(
                    selected => this.getOptionValue(selected) === value
                );
            } else {
                return this.selectedValue === value;
            }
        },

        navigateOptions(direction) {
            if (direction === 'up') {
                this.highlightedIndex = Math.max(0, this.highlightedIndex - 1);
            } else {
                this.highlightedIndex = Math.min(
                    this.filteredOptions.length - 1,
                    this.highlightedIndex + 1
                );
            }

            // Scroll to highlighted option
            this.$nextTick(() => {
                const highlightedEl = this.$el.querySelector('.simple-select-option-highlighted');
                const menuEl = this.$el.querySelector('.simple-select-menu');

                if (highlightedEl && menuEl) {
                    const menuRect = menuEl.getBoundingClientRect();
                    const optionRect = highlightedEl.getBoundingClientRect();

                    if (optionRect.bottom > menuRect.bottom) {
                        menuEl.scrollTop += optionRect.bottom - menuRect.bottom;
                    } else if (optionRect.top < menuRect.top) {
                        menuEl.scrollTop -= menuRect.top - optionRect.top;
                    }
                }
            });
        },

        get hasSelection() {
            if (this.multiple) {
                return this.selectedOptions.length > 0;
            } else {
                return this.selectedOption !== null;
            }
        }
    }));
});
