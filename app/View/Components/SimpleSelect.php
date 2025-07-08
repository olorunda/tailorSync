<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SimpleSelect extends Component
{
    /**
     * The options for the select.
     *
     * @var array
     */
    public $options;

    /**
     * The placeholder text.
     *
     * @var string
     */
    public $placeholder;

    /**
     * The option label key.
     *
     * @var string
     */
    public $optionLabel;

    /**
     * The option value key.
     *
     * @var string
     */
    public $optionValue;

    /**
     * The text to display when no results are found.
     *
     * @var string
     */
    public $noResultsText;

    /**
     * Whether the select is searchable.
     *
     * @var bool
     */
    public $searchable;

    /**
     * Whether the select is clearable.
     *
     * @var bool
     */
    public $clearable;

    /**
     * Whether the select allows multiple selections.
     *
     * @var bool
     */
    public $multiple;

    /**
     * Whether the select is disabled.
     *
     * @var bool
     */
    public $disabled;

    /**
     * The maximum number of items that can be selected.
     *
     * @var int|null
     */
    public $maxSelection;

    /**
     * The message to display when the maximum selection is reached.
     *
     * @var string
     */
    public $maxSelectionMessage;

    /**
     * The maximum height of the dropdown.
     *
     * @var string
     */
    public $maxHeight;

    /**
     * The position of the dropdown.
     *
     * @var string
     */
    public $position;

    /**
     * Whether the select is required.
     *
     * @var bool
     */
    public $required;

    /**
     * Create a new component instance.
     *
     * @param array $options
     * @param string $placeholder
     * @param string $optionLabel
     * @param string $optionValue
     * @param string $noResultsText
     * @param bool $searchable
     * @param bool $clearable
     * @param bool $multiple
     * @param bool $disabled
     * @param int|null $maxSelection
     * @param string $maxSelectionMessage
     * @param string $maxHeight
     * @param string $position
     * @param bool $required
     * @return void
     */
    public function __construct(
        $options = [],
        $placeholder = 'Select an option',
        $optionLabel = 'name',
        $optionValue = 'id',
        $noResultsText = 'No results found',
        $searchable = true,
        $clearable = true,
        $multiple = false,
        $disabled = false,
        $maxSelection = null,
        $maxSelectionMessage = 'You can only select {max} items',
        $maxHeight = '300px',
        $position = 'auto',
        $required = false
    ) {
        $this->options = $options;
        $this->placeholder = $placeholder;
        $this->optionLabel = $optionLabel;
        $this->optionValue = $optionValue;
        $this->noResultsText = $noResultsText;
        $this->searchable = $searchable;
        $this->clearable = $clearable;
        $this->multiple = $multiple;
        $this->disabled = $disabled;
        $this->maxSelection = $maxSelection;
        $this->maxSelectionMessage = $maxSelectionMessage;
        $this->maxHeight = $maxHeight;
        $this->position = $position;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.simple-select.index');
    }
}
