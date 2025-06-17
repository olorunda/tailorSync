// Import Tom Select
import TomSelect from 'tom-select';
//
// // Function to initialize Tom Select on an element
// function initializeTomSelect(select) {
//     // Skip if already initialized or has a specific class to exclude
//     if (select.tomselect || select.classList.contains('no-tom-select')) {
//         return;
//     }
//
//     new TomSelect(select, {
//         plugins: {
//             clear_button: {
//                 title: 'Remove selection',
//             }
//         },
//
//
//         // // Allow searching within options
//         allowEmptyOption: true,
//         // // Show dropdown even with single option
//         dropdownParent: 'body',
//         // // Customize placeholder text
//         placeholder: select.getAttribute('placeholder') || 'Search...',
//         // // Enable searching
//         persist: false,
//         // // Create items when no matches
//         createOnBlur: false,
//         // create: false
//     });
// }
//
// // Function to initialize Tom Select on all select elements
// function initializeAllSelects() {
//     const selectElements = document.querySelectorAll('select');
//     selectElements.forEach(initializeTomSelect);
// }
//
// // Initialize when DOM is loaded
// document.addEventListener('DOMContentLoaded', initializeAllSelects);
//
// // Initialize when Livewire updates the DOM
// document.addEventListener('livewire:navigated', initializeAllSelects);
//
// // Initialize when Livewire updates a component
// document.addEventListener('livewire:init', () => {
//     Livewire.hook('morph.updated', ({ el }) => {
//         // Find all select elements within the updated element
//         const selectElements = el.querySelectorAll('select');
//         selectElements.forEach(initializeTomSelect);
//     });
// });
