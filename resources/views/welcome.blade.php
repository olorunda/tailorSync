<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>TailorSync - Tailoring Management System</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-zinc-900 dark:to-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-screen">
        <header class="w-full max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
            <div class="flex items-center">
                <div class="text-3xl font-bold text-orange-600 dark:text-orange-500">TailorSync</div>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-2 text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 rounded-md text-sm font-medium transition-colors"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition-colors">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <!-- Hero Section -->
            <section class="flex flex-col lg:flex-row items-center gap-12 mb-20">
                <div class="lg:w-1/2">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-orange-600 dark:text-orange-500">
                        Streamline Your Tailoring Business
                    </h1>
                    <p class="text-lg mb-8 text-zinc-700 dark:text-zinc-300">
                        TailorSync is a comprehensive management system designed specifically for tailors and fashion designers. Manage clients, measurements, orders, inventory, and finances all in one place.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-md font-medium transition-colors">
                                Get Started
                            </a>
                        @endif
                        <a href="#features" class="px-6 py-3 border border-orange-600 text-orange-600 hover:bg-orange-100 dark:hover:bg-orange-900/20 rounded-md font-medium transition-colors">
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <div class="relative rounded-xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1556905055-8f358a7a47b2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80"
                             alt="Tailor working" class="w-full h-auto">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                            <div class="p-6 text-white">
                                <p class="text-xl font-medium">Designed for the modern tailor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="mb-20">
                <h2 class="text-3xl font-bold mb-12 text-center text-orange-600 dark:text-orange-500">Key Features</h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Client Measurement Vault</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Store and track client measurements with ease. View measurement history and attach client photos for reference.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Order Management Dashboard</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Manage orders with a Kanban-style board. Track status from pending to paid, with all order details in one place.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Design Board</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Upload sketches and inspiration photos. Tag fabrics and styles to each design and organize them into collections.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Inventory Tracker</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Manage fabric, accessories, and tools inventory. Get low stock notifications and link items to specific orders.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Client Communication</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Built-in messaging system for client communications. Send order updates and delivery reminders with ease.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Finance & Invoicing</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Generate professional invoices, track payments, and monitor expenses. View financial reports at a glance.</p>
                    </div>
                </div>
            </section>

            <!-- Workflow Section -->
            <section class="mb-20">
                <h2 class="text-3xl font-bold mb-12 text-center text-orange-600 dark:text-orange-500">Streamlined Workflow</h2>

                <div class="relative">
                    <!-- Timeline -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-orange-200 dark:bg-orange-900/30"></div>

                    <!-- Steps -->
                    <div class="relative z-10">
                        <!-- Step 1 -->
                        <div class="flex flex-col md:flex-row items-center mb-12">
                            <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                <h3 class="text-xl font-semibold mb-2">Client Registration</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Add new clients and record their measurements in the system.</p>
                            </div>
                            <div class="bg-orange-600 dark:bg-orange-500 rounded-full h-8 w-8 flex items-center justify-center z-10">
                                <span class="text-white font-bold">1</span>
                            </div>
                            <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col md:flex-row items-center mb-12">
                            <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                            <div class="bg-orange-600 dark:bg-orange-500 rounded-full h-8 w-8 flex items-center justify-center z-10">
                                <span class="text-white font-bold">2</span>
                            </div>
                            <div class="md:w-1/2 md:pl-12 md:text-left mb-6 md:mb-0">
                                <h3 class="text-xl font-semibold mb-2">Design Creation</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Upload design sketches and tag fabrics and styles for reference.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col md:flex-row items-center mb-12">
                            <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                <h3 class="text-xl font-semibold mb-2">Order Management</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Create orders with client details, design specifications, and due dates.</p>
                            </div>
                            <div class="bg-orange-600 dark:bg-orange-500 rounded-full h-8 w-8 flex items-center justify-center z-10">
                                <span class="text-white font-bold">3</span>
                            </div>
                            <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex flex-col md:flex-row items-center mb-12">
                            <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                            <div class="bg-orange-600 dark:bg-orange-500 rounded-full h-8 w-8 flex items-center justify-center z-10">
                                <span class="text-white font-bold">4</span>
                            </div>
                            <div class="md:w-1/2 md:pl-12 md:text-left mb-6 md:mb-0">
                                <h3 class="text-xl font-semibold mb-2">Production Tracking</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Track order progress through cutting, sewing, fitting, and delivery stages.</p>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="flex flex-col md:flex-row items-center">
                            <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                <h3 class="text-xl font-semibold mb-2">Invoicing & Payment</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Generate invoices, record payments, and track financial performance.</p>
                            </div>
                            <div class="bg-orange-600 dark:bg-orange-500 rounded-full h-8 w-8 flex items-center justify-center z-10">
                                <span class="text-white font-bold">5</span>
                            </div>
                            <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="bg-gradient-to-r from-orange-600 to-amber-500 text-white rounded-xl p-8 md:p-12 shadow-lg">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl font-bold mb-4">Ready to Transform Your Tailoring Business?</h2>
                    <p class="text-lg mb-8 opacity-90">Join TailorSync today and experience the difference a dedicated management system can make.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-8 py-3 bg-white text-orange-600 hover:bg-orange-50 rounded-md font-medium transition-colors">
                                Sign Up Now
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="px-8 py-3 border border-white text-white hover:bg-white/10 rounded-md font-medium transition-colors">
                                Log In
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-zinc-100 dark:bg-zinc-900 py-12 mt-20">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-500 mb-2">TailorSync</div>
                        <p class="text-zinc-600 dark:text-zinc-400">Tailoring Management System</p>
                    </div>
                    <div class="flex flex-col md:flex-row gap-8">
                        <div>
                            <h3 class="font-semibold mb-3 text-zinc-800 dark:text-zinc-200">Features</h3>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li>Client Management</li>
                                <li>Order Tracking</li>
                                <li>Design Board</li>
                                <li>Inventory Management</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-3 text-zinc-800 dark:text-zinc-200">Resources</h3>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li>Documentation</li>
                                <li>Support</li>
                                <li>Privacy Policy</li>
                                <li>Terms of Service</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="border-t border-zinc-200 dark:border-zinc-800 mt-8 pt-8 text-center text-zinc-500 dark:text-zinc-500">
                    <p>&copy; {{ date('Y') }} TailorSync. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
