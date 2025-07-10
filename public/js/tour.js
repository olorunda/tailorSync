/**
 * TailorFit Page-Specific Tour
 *
 * This file contains the tour steps and initialization logic for the TailorFit application tour.
 * Each page has its own specific tour that highlights the relevant elements on that page.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if the tour should be shown (this variable will be set in the layout)
    if (typeof showTour !== 'undefined' && showTour) {
        // Get the current page from the URL
        const currentPage = getCurrentPage();

        // Check if this page's tour has been completed
        checkTourCompletionForPage(currentPage, function(completed) {

            if (!completed) {
                initTour();
            }
        });
    }
});

// Listen for Livewire navigation events to trigger the tour on page navigation
document.addEventListener('livewire:navigated', function() {
    // Check if the tour should be shown (this variable will be set in the layout)
  //  if (typeof showTour !== 'undefined' && showTour) {
        // Get the current page from the URL
        const currentPage = getCurrentPage();

        // Check if this page's tour has been completed
        checkTourCompletionForPage(currentPage, function(completed) {
            if (!completed) {
                initTour();
            }
        });
  //  }
});

// Global variable to track the current tour instance
let currentTour = null;

/**
 * Initialize the tour
 */
function initTour() {
    // If a tour is already running, exit it first
    if (currentTour) {
        currentTour.exit();
        currentTour = null;
    }

    // Get the current page from the URL
    const currentPage = getCurrentPage();

    // Get the tour steps for the current page
    let steps = getTourStepsForPage(currentPage);

    // If there are no steps for this page, don't show the tour
    if (!steps || steps.length === 0) {
        return;
    }

    // Filter out steps with elements that don't exist on the page
    steps = filterValidSteps(steps);

    // If there are no valid steps left, don't show the tour
    if (steps.length === 0) {
        console.log('No valid tour steps found for this page');
        return;
    }

    // Create the tour instance
    const tour = introJs();

    // Configure the tour
    tour.setOptions({
        steps: steps,
        showStepNumbers: true,
        showBullets: true,
        showProgress: true,
        overlayOpacity: 0.8,
        scrollToElement: true,
        disableInteraction: false,
        exitOnOverlayClick: false,
        exitOnEsc: false,
        doneLabel: 'Complete Tour',
        nextLabel: 'Next Step →',
        prevLabel: '← Previous Step',
    });

    // Add event listeners
    tour.oncomplete(function() {
        completeTour();
        currentTour = null;
    });

    tour.onexit(function() {
        completeTour();
        currentTour = null;
    });

    // Store the current tour instance
    currentTour = tour;

    // Start the tour
    tour.start();
}

/**
 * Filter out steps with elements that don't exist on the page
 */
function filterValidSteps(steps) {
    return steps.filter(step => {
        // If the step doesn't have an element selector, it's valid
        if (!step.element) {
            return true;
        }

        // Check if the element exists on the page
        try {
            const element = document.querySelector(step.element);
            return element !== null;
        } catch (error) {
            console.warn(`Invalid selector in tour step: ${step.element}`);
            return false;
        }
    });
}

/**
 * Get the current page from the URL
 */
function getCurrentPage() {
    // Get the path from the URL
    const path = window.location.pathname;

    // Remove leading and trailing slashes
    const cleanPath = path.replace(/^\/|\/$/g, '');

    // Split the path into segments
    const segments = cleanPath.split('/');

    // If there are no segments, we're on the dashboard
    if (!segments[0]) {
        return 'dashboard';
    }

    // Get the base page name (first segment)
    const basePage = segments[0];

    // Check if we're on a detail page (show, edit, create)
    if (segments.length > 1) {
        const action = segments[1];

        // Check for create page
        if (action === 'create') {
            return `${basePage}_create`;
        }

        // Check for edit page (segments[2] would be the ID, segments[3] would be 'edit')
        if (segments.length > 2 && segments[2] === 'edit') {
            return `${basePage}_edit`;
        }

        // Check for show page (if second segment is numeric, it's likely a show page)
        if (!isNaN(parseInt(action))) {
            return `${basePage}_show`;
        }
    }

    // Return the base page name
    return basePage;
}

/**
 * Get the tour steps for the specified page
 */
function getTourStepsForPage(page) {
    // Define tour steps for each page
    const tourSteps = {
        // Dashboard page tour
        'dashboard': [
            {
                title: "Welcome to TailorFit",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to TailorFit!</h3>" +
                       "<p>This tour will guide you through the dashboard features to help you manage your tailor shop effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Dashboard Overview",
                intro: "This is your dashboard. It provides an overview of your business's key metrics and recent activities."
            },
            {
                element: ".grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-4.mb-6",
                title: "Financial Summary",
                intro: "These cards show your financial summary, including total revenue, expenses, net profit, and pending payments. Check these daily to keep track of your business health."
            },
            {
                element: "#revenue-expenses-chart",
                title: "Revenue vs Expenses Chart",
                intro: "This chart visualizes your revenue and expenses over time. Use it to identify trends and make informed business decisions."
            },
            {
                element: "#orders-status-chart",
                title: "Orders by Status Chart",
                intro: "This chart shows the distribution of your orders by status. It helps you understand your current workload at a glance."
            },
            {
                element: "select[wire\\:model\\.live='timeframe']",
                title: "Time Frame Selection",
                intro: "Use this dropdown to change the time frame for the displayed data. Try different time frames to get insights into your business performance over different periods."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Dashboard Tour Complete!</h3>" +
                       "<p>You've completed the dashboard tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Clients page tour
        'clients': [
            {
                title: "Client Management",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Client Management!</h3>" +
                       "<p>This tour will guide you through the client management features to help you manage your client information effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Clients Overview",
                intro: "This is your clients page. It provides a list of all your clients and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Add New Client",
                intro: "Click this button to add a new client to your database."
            },
            {
                element: "input[type='search']",
                title: "Search Clients",
                intro: "Use this search box to quickly find clients by name, email, or phone number."
            },
            {
                element: "table.responsive-table",
                title: "Client List",
                intro: "This table shows all your clients. Click on a client's name to view their details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Client Management Tour Complete!</h3>" +
                       "<p>You've completed the client management tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Client Create page tour
        'clients_create': [
            {
                title: "Add New Client",
                intro: "<div class='tour-intro'>" +
                       "<h3>Adding a New Client</h3>" +
                       "<p>This tour will guide you through the process of adding a new client to your database.</p>" +
                       "</div>"
            },
            {
                element: "form",
                title: "Client Information Form",
                intro: "Fill out this form with your client's information. Fields marked with an asterisk (*) are required."
            },
            {
                element: "input[name='name']",
                title: "Client Name",
                intro: "Enter the client's full name here. This is a required field."
            },
            {
                element: "input[name='email']",
                title: "Client Email",
                intro: "Enter the client's email address here. This is used for sending invoices and appointment reminders."
            },
            {
                element: "input[name='phone']",
                title: "Client Phone",
                intro: "Enter the client's phone number here. This is used for contacting the client directly."
            },
            {
                element: "textarea[name='address']",
                title: "Client Address",
                intro: "Enter the client's address here. This is useful for delivery or home appointments."
            },
            {
                element: "textarea[name='notes']",
                title: "Client Notes",
                intro: "Add any additional notes about the client here, such as preferences or special requirements."
            },
            {
                element: "button[type='submit']",
                title: "Save Client",
                intro: "Click this button to save the client information to your database."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Add Client Tour Complete!</h3>" +
                       "<p>You've completed the add client tour. Now you can add your client's information and save it to your database.</p>" +
                       "</div>"
            }
        ],

        // Client Show page tour
        'clients_show': [
            {
                title: "Client Details",
                intro: "<div class='tour-intro'>" +
                       "<h3>Client Details Page</h3>" +
                       "<p>This tour will guide you through the client details page where you can view and manage all information related to this client.</p>" +
                       "</div>"
            },
            {
                element: ".client-header",
                title: "Client Header",
                intro: "This section shows the client's basic information and provides quick action buttons."
            },
            {
                element: ".client-actions",
                title: "Client Actions",
                intro: "These buttons allow you to edit the client, create orders, schedule appointments, and more for this client."
            },
            {
                element: ".client-tabs",
                title: "Client Tabs",
                intro: "These tabs organize different types of information about the client, such as orders, appointments, measurements, and more."
            },
            {
                element: ".client-orders",
                title: "Client Orders",
                intro: "This section shows all orders for this client. You can view, edit, or create new orders from here."
            },
            {
                element: ".client-measurements",
                title: "Client Measurements",
                intro: "This section shows all measurements for this client. You can view, edit, or add new measurements from here."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Client Details Tour Complete!</h3>" +
                       "<p>You've completed the client details tour. Now you can manage all aspects of this client's information from this page.</p>" +
                       "</div>"
            }
        ],

        // Orders page tour
        'orders': [
            {
                title: "Order Management",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Order Management!</h3>" +
                       "<p>This tour will guide you through the order management features to help you track and manage your orders effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Orders Overview",
                intro: "This is your orders page. It provides a list of all your orders and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Create New Order",
                intro: "Click this button to create a new order for a client."
            },
            {
                element: "input[type='search']",
                title: "Search Orders",
                intro: "Use this search box to quickly find orders by order number, client name, or status."
            },
            {
                element: "table.responsive-table",
                title: "Order List",
                intro: "This table shows all your orders. Click on an order number to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Order Management Tour Complete!</h3>" +
                       "<p>You've completed the order management tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Order Create page tour
        'orders_create': [
            {
                title: "Create New Order",
                intro: "<div class='tour-intro'>" +
                       "<h3>Creating a New Order</h3>" +
                       "<p>This tour will guide you through the process of creating a new order for a client.</p>" +
                       "</div>"
            },
            {
                element: "form",
                title: "Order Form",
                intro: "Fill out this form with the order details. Fields marked with an asterisk (*) are required."
            },
            {
                element: ".client-select",
                title: "Select Client",
                intro: "Select the client for this order. You can search for clients by name or email."
            },
            {
                element: "input[name='due_date']",
                title: "Due Date",
                intro: "Set the due date for this order. This helps you manage your workflow and meet client expectations."
            },
            {
                element: ".order-items",
                title: "Order Items",
                intro: "Add the items for this order. For each item, specify the description, quantity, and price."
            },
            {
                element: ".add-item-button",
                title: "Add Item",
                intro: "Click this button to add more items to the order."
            },
            {
                element: ".order-status",
                title: "Order Status",
                intro: "Set the initial status of the order. You can update this as the order progresses."
            },
            {
                element: "textarea[name='notes']",
                title: "Order Notes",
                intro: "Add any additional notes about the order here, such as special instructions or requirements."
            },
            {
                element: "button[type='submit']",
                title: "Save Order",
                intro: "Click this button to save the order to your database."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Create Order Tour Complete!</h3>" +
                       "<p>You've completed the create order tour. Now you can create a new order for your client.</p>" +
                       "</div>"
            }
        ],

        // Order Show page tour
        'orders_show': [
            {
                title: "Order Details",
                intro: "<div class='tour-intro'>" +
                       "<h3>Order Details Page</h3>" +
                       "<p>This tour will guide you through the order details page where you can view and manage all information related to this order.</p>" +
                       "</div>"
            },
            {
                element: ".order-header",
                title: "Order Header",
                intro: "This section shows the order's basic information, including order number, client, and status."
            },
            {
                element: ".order-actions",
                title: "Order Actions",
                intro: "These buttons allow you to edit the order, create an invoice, update the status, and more."
            },
            {
                element: ".order-items",
                title: "Order Items",
                intro: "This section shows all items in this order, including descriptions, quantities, and prices."
            },
            {
                element: ".order-summary",
                title: "Order Summary",
                intro: "This section shows the order totals, including subtotal, tax, and total amount."
            },
            {
                element: ".order-timeline",
                title: "Order Timeline",
                intro: "This section shows the history of the order, including status changes and notes."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Order Details Tour Complete!</h3>" +
                       "<p>You've completed the order details tour. Now you can manage all aspects of this order from this page.</p>" +
                       "</div>"
            }
        ],

        // Appointments page tour
        'appointments': [
            {
                title: "Appointment Scheduling",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Appointment Scheduling!</h3>" +
                       "<p>This tour will guide you through the appointment scheduling features to help you manage your appointments effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Appointments Overview",
                intro: "This is your appointments page. It provides a calendar view of all your appointments and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Schedule New Appointment",
                intro: "Click this button to schedule a new appointment for a client."
            },
            {
                element: ".calendar-view",
                title: "Calendar View",
                intro: "This calendar shows all your appointments. Click on a date to view appointments for that day."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Appointment Scheduling Tour Complete!</h3>" +
                       "<p>You've completed the appointment scheduling tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Invoices page tour
        'invoices': [
            {
                title: "Invoicing",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Invoicing!</h3>" +
                       "<p>This tour will guide you through the invoicing features to help you manage your invoices effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Invoices Overview",
                intro: "This is your invoices page. It provides a list of all your invoices and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Create New Invoice",
                intro: "Click this button to create a new invoice for a client."
            },
            {
                element: "input[type='search']",
                title: "Search Invoices",
                intro: "Use this search box to quickly find invoices by invoice number, client name, or status."
            },
            {
                element: "table.responsive-table",
                title: "Invoice List",
                intro: "This table shows all your invoices. Click on an invoice number to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Invoicing Tour Complete!</h3>" +
                       "<p>You've completed the invoicing tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Invoice Create page tour
        'invoices_create': [
            {
                title: "Create New Invoice",
                intro: "<div class='tour-intro'>" +
                       "<h3>Creating a New Invoice</h3>" +
                       "<p>This tour will guide you through the process of creating a new invoice for a client.</p>" +
                       "</div>"
            },
            {
                element: "form",
                title: "Invoice Form",
                intro: "Fill out this form with the invoice details. Fields marked with an asterisk (*) are required."
            },
            {
                element: ".client-select",
                title: "Select Client",
                intro: "Select the client for this invoice. You can search for clients by name or email."
            },
            {
                element: ".order-select",
                title: "Select Order",
                intro: "Optionally, you can select an order to create an invoice from. This will automatically fill in the invoice items."
            },
            {
                element: "input[name='invoice_date']",
                title: "Invoice Date",
                intro: "Set the invoice date. This is typically the date the invoice is created."
            },
            {
                element: "input[name='due_date']",
                title: "Due Date",
                intro: "Set the due date for this invoice. This is the date by which payment is expected."
            },
            {
                element: ".invoice-items",
                title: "Invoice Items",
                intro: "Add the items for this invoice. For each item, specify the description, quantity, and price."
            },
            {
                element: ".add-item-button",
                title: "Add Item",
                intro: "Click this button to add more items to the invoice."
            },
            {
                element: ".tax-rate",
                title: "Tax Rate",
                intro: "Set the tax rate for this invoice. This will be applied to the subtotal."
            },
            {
                element: ".discount",
                title: "Discount",
                intro: "Optionally, you can apply a discount to this invoice. You can specify a percentage or a fixed amount."
            },
            {
                element: "textarea[name='notes']",
                title: "Invoice Notes",
                intro: "Add any additional notes about the invoice here, such as payment instructions or terms."
            },
            {
                element: "button[type='submit']",
                title: "Save Invoice",
                intro: "Click this button to save the invoice to your database."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Create Invoice Tour Complete!</h3>" +
                       "<p>You've completed the create invoice tour. Now you can create a new invoice for your client.</p>" +
                       "</div>"
            }
        ],

        // Invoice Show page tour
        'invoices_show': [
            {
                title: "Invoice Details",
                intro: "<div class='tour-intro'>" +
                       "<h3>Invoice Details Page</h3>" +
                       "<p>This tour will guide you through the invoice details page where you can view and manage all information related to this invoice.</p>" +
                       "</div>"
            },
            {
                element: ".invoice-header",
                title: "Invoice Header",
                intro: "This section shows the invoice's basic information, including invoice number, client, and status."
            },
            {
                element: ".invoice-actions",
                title: "Invoice Actions",
                intro: "These buttons allow you to edit the invoice, record a payment, download a PDF, send to client, and more."
            },
            {
                element: ".invoice-status",
                title: "Invoice Status",
                intro: "This shows the current payment status of the invoice (unpaid, partial, paid, or overdue)."
            },
            {
                element: ".invoice-items",
                title: "Invoice Items",
                intro: "This section shows all items in this invoice, including descriptions, quantities, and prices."
            },
            {
                element: ".invoice-summary",
                title: "Invoice Summary",
                intro: "This section shows the invoice totals, including subtotal, tax, discount, and total amount."
            },
            {
                element: ".invoice-payments",
                title: "Invoice Payments",
                intro: "This section shows all payments made against this invoice."
            },
            {
                element: ".record-payment-button",
                title: "Record Payment",
                intro: "Click this button to record a payment for this invoice."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Invoice Details Tour Complete!</h3>" +
                       "<p>You've completed the invoice details tour. Now you can manage all aspects of this invoice from this page.</p>" +
                       "</div>"
            }
        ],

        // Invoice Edit page tour
        'invoices_edit': [
            {
                title: "Edit Invoice",
                intro: "<div class='tour-intro'>" +
                       "<h3>Editing an Invoice</h3>" +
                       "<p>This tour will guide you through the process of editing an existing invoice.</p>" +
                       "</div>"
            },
            {
                element: "form",
                title: "Invoice Form",
                intro: "Update the invoice details as needed. Fields marked with an asterisk (*) are required."
            },
            {
                element: ".invoice-status-select",
                title: "Update Status",
                intro: "You can update the invoice status here (unpaid, partial, paid, or overdue)."
            },
            {
                element: ".invoice-items",
                title: "Edit Invoice Items",
                intro: "You can edit existing items, add new items, or remove items from the invoice."
            },
            {
                element: ".tax-rate",
                title: "Update Tax Rate",
                intro: "You can update the tax rate for this invoice if needed."
            },
            {
                element: ".discount",
                title: "Update Discount",
                intro: "You can update or add a discount to this invoice if needed."
            },
            {
                element: "textarea[name='notes']",
                title: "Update Notes",
                intro: "You can update the invoice notes here."
            },
            {
                element: "button[type='submit']",
                title: "Save Changes",
                intro: "Click this button to save your changes to the invoice."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Edit Invoice Tour Complete!</h3>" +
                       "<p>You've completed the edit invoice tour. Now you can save your changes to update the invoice.</p>" +
                       "</div>"
            }
        ],

        // Payments page tour
        'payments': [
            {
                title: "Payments",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Payments!</h3>" +
                       "<p>This tour will guide you through the payment features to help you track and manage your payments effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Payments Overview",
                intro: "This is your payments page. It provides a list of all your payments and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Record New Payment",
                intro: "Click this button to record a new payment from a client."
            },
            {
                element: "input[type='search']",
                title: "Search Payments",
                intro: "Use this search box to quickly find payments by reference number, client name, or amount."
            },
            {
                element: "table.responsive-table",
                title: "Payment List",
                intro: "This table shows all your payments. Click on a payment to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Payments Tour Complete!</h3>" +
                       "<p>You've completed the payments tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Designs page tour
        'designs': [
            {
                title: "Designs",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Designs!</h3>" +
                       "<p>This tour will guide you through the design features to help you manage your clothing designs and patterns effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Designs Overview",
                intro: "This is your designs page. It provides a gallery of all your designs and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Add New Design",
                intro: "Click this button to add a new design to your library."
            },
            {
                element: "input[type='search']",
                title: "Search Designs",
                intro: "Use this search box to quickly find designs by name or tag."
            },
            {
                element: ".design-gallery",
                title: "Design Gallery",
                intro: "This gallery shows all your designs. Click on a design to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Designs Tour Complete!</h3>" +
                       "<p>You've completed the designs tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Inventory page tour
        'inventory': [
            {
                title: "Inventory Management",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Inventory Management!</h3>" +
                       "<p>This tour will guide you through the inventory features to help you track and manage your materials effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Inventory Overview",
                intro: "This is your inventory page. It provides a list of all your inventory items and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Add New Item",
                intro: "Click this button to add a new item to your inventory."
            },
            {
                element: "input[type='search']",
                title: "Search Inventory",
                intro: "Use this search box to quickly find inventory items by name or category."
            },
            {
                element: "table.responsive-table",
                title: "Inventory List",
                intro: "This table shows all your inventory items. Click on an item to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Inventory Management Tour Complete!</h3>" +
                       "<p>You've completed the inventory management tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Messages page tour
        'messages': [
            {
                title: "Messaging",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Messaging!</h3>" +
                       "<p>This tour will guide you through the messaging features to help you communicate with your clients and team members effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Messages Overview",
                intro: "This is your messages page. It provides a list of all your conversations and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "New Message",
                intro: "Click this button to start a new conversation with a client or team member."
            },
            {
                element: "input[type='search']",
                title: "Search Messages",
                intro: "Use this search box to quickly find messages by content or sender."
            },
            {
                element: ".message-list",
                title: "Message List",
                intro: "This list shows all your conversations. Click on a conversation to view its messages."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Messaging Tour Complete!</h3>" +
                       "<p>You've completed the messaging tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Tasks page tour
        'tasks': [
            {
                title: "Tasks",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Tasks!</h3>" +
                       "<p>This tour will guide you through the task features to help you manage your workshop workflow effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Tasks Overview",
                intro: "This is your tasks page. It provides a list of all your tasks and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Create New Task",
                intro: "Click this button to create a new task for yourself or a team member."
            },
            {
                element: "input[type='search']",
                title: "Search Tasks",
                intro: "Use this search box to quickly find tasks by title or assignee."
            },
            {
                element: "table.responsive-table",
                title: "Task List",
                intro: "This table shows all your tasks. Click on a task to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Tasks Tour Complete!</h3>" +
                       "<p>You've completed the tasks tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ],

        // Expenses page tour
        'expenses': [
            {
                title: "Expenses",
                intro: "<div class='tour-intro'>" +
                       "<h3>Welcome to Expenses!</h3>" +
                       "<p>This tour will guide you through the expense features to help you track and manage your business expenses effectively.</p>" +
                       "</div>"
            },
            {
                element: ".text-2xl.font-bold.text-zinc-900",
                title: "Expenses Overview",
                intro: "This is your expenses page. It provides a list of all your expenses and tools to manage them."
            },
            {
                element: "button.bg-primary-600",
                title: "Record New Expense",
                intro: "Click this button to record a new business expense."
            },
            {
                element: "input[type='search']",
                title: "Search Expenses",
                intro: "Use this search box to quickly find expenses by description or category."
            },
            {
                element: "table.responsive-table",
                title: "Expense List",
                intro: "This table shows all your expenses. Click on an expense to view its details."
            },
            {
                title: "Tour Complete",
                intro: "<div class='tour-complete'>" +
                       "<h3>🎉 Expenses Tour Complete!</h3>" +
                       "<p>You've completed the expenses tour. Explore other sections of the application to learn more about their features.</p>" +
                       "</div>"
            }
        ]
    };

    // Return the steps for the specified page, or an empty array if the page doesn't have a tour
    return tourSteps[page] || [];
}

/**
 * Check if the tour for a specific page has been completed
 */
function checkTourCompletionForPage(pageName, callback) {
    // Send an AJAX request to check if the tour for this page has been completed
    fetch(`/api/check-tour-completion?page_name=${pageName}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        callback(data.completed);
    })
    .catch(error => {
        console.error('Error checking tour completion:', error);
        // If there's an error, assume the tour hasn't been completed
        callback(false);
    });
}

/**
 * Mark the tour as completed for the current page
 */
function completeTour() {
    // Get the current page from the URL
    const currentPage = getCurrentPage();

    // Update the showTour variable to prevent tours from showing on subsequent page navigations
    window.showTour = false;

    // Send an AJAX request to mark the tour as completed for this page
    fetch('/tour/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            page_name: currentPage
        }),
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`Tour completed successfully for page: ${currentPage}`);
        }
    })
    .catch(error => {
        console.error('Error completing tour:', error);
    });
}
