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
        <link href="https://fonts.bunny.net/css?family=playfair-display:700" rel="stylesheet" />

        <!-- Styles -->
        <style>
            [x-cloak] { display: none !important; }

            /* Parallax base styles */
            .parallax-section {
                position: relative;
                overflow: hidden;
                background-attachment: fixed;
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
            }

            .parallax-content {
                position: relative;
                z-index: 2;
            }

            .parallax-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6));
                z-index: 1;
            }

            /* Animated elements */
            .fade-in-up {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.6s ease-out, transform 0.6s ease-out;
            }

            .fade-in-up.visible {
                opacity: 1;
                transform: translateY(0);
            }

            /* Custom font styles */
            .font-playfair {
                font-family: 'Playfair Display', serif;
            }

            /* Floating animation */
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }

            .floating {
                animation: float 4s ease-in-out infinite;
            }

            /* Gradient text */
            .gradient-text {
                background: linear-gradient(to right, #f97316, #f59e0b);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                color: transparent;
            }

            /* Glowing effect */
            .glow {
                box-shadow: 0 0 15px rgba(249, 115, 22, 0.5);
                transition: box-shadow 0.3s ease;
            }

            .glow:hover {
                box-shadow: 0 0 25px rgba(249, 115, 22, 0.8);
            }

            /* 3D Card effect */
            .card-3d {
                transition: transform 0.5s ease;
                transform-style: preserve-3d;
            }

            .card-3d:hover {
                transform: translateY(-10px) rotateX(5deg);
            }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:from-zinc-900 dark:to-zinc-800 text-zinc-900 dark:text-zinc-100 min-h-screen">
        <header class="fixed w-full z-50 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <div class="text-3xl font-bold gradient-text font-playfair">TailorSync</div>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-block px-5 py-2 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white rounded-md text-sm font-medium transition-colors glow"
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
                                    class="inline-block px-5 py-2 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white rounded-md text-sm font-medium transition-colors glow">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main>
            <!-- Hero Section with Parallax -->
            <section class="parallax-section min-h-screen flex items-center" style="background-image: url('https://images.unsplash.com/photo-1556905055-8f358a7a47b2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80');">
                <div class="parallax-overlay"></div>
                <div class="parallax-content max-w-7xl mx-auto px-4 py-20 pt-32">
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div class="text-white">
                            <h1 class="text-5xl md:text-6xl font-bold mb-6 font-playfair leading-tight">
                                <span class="block">Elevate Your</span>
                                <span class="block gradient-text">Tailoring Business</span>
                            </h1>
                            <p class="text-xl mb-8 text-zinc-100 max-w-xl">
                                TailorSync is a comprehensive management system designed specifically for tailors and fashion designers. Manage clients, measurements, orders, inventory, and finances all in one place.
                            </p>
                            <div class="flex flex-wrap gap-4">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-8 py-4 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white rounded-md font-medium transition-colors glow">
                                        Get Started
                                    </a>
                                @endif
                                <a href="#features" class="px-8 py-4 border-2 border-white text-white hover:bg-white/10 rounded-md font-medium transition-colors">
                                    Learn More
                                </a>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="relative bg-white/10 backdrop-blur-md p-6 rounded-2xl shadow-2xl border border-white/20 floating">
                                <div class="absolute -top-4 -right-4 bg-orange-600 text-white text-sm px-3 py-1 rounded-full">
                                    New
                                </div>
                                <h3 class="text-xl font-semibold mb-4 text-white">Designed for the modern tailor</h3>
                                <ul class="space-y-3 text-zinc-100">
                                    <li class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Client Management</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Order Tracking</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Measurement Tracking</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Inventory Management</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section with Parallax -->
            <section id="features" class="py-20 bg-gradient-to-b from-white to-orange-50 dark:from-zinc-900 dark:to-zinc-800">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-bold mb-4 font-playfair gradient-text">Powerful Features</h2>
                        <p class="text-xl text-zinc-700 dark:text-zinc-300 max-w-3xl mx-auto">
                            Everything you need to manage your tailoring business efficiently in one place
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Client Measurement Vault</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Store and track client measurements with ease. View measurement history and attach client photos for reference.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Detailed measurement forms</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Measurement history tracking</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Client photo attachments</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Order Management Dashboard</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Manage orders with a Kanban-style board. Track status from pending to paid, with all order details in one place.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Visual Kanban board</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Status tracking & updates</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Deadline notifications</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Design Board</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Upload sketches and inspiration photos. Tag fabrics and styles to each design and organize them into collections.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Visual design gallery</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Fabric & style tagging</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Collection organization</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Feature 4 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Inventory Tracker</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Manage fabric, accessories, and tools inventory. Get low stock notifications and link items to specific orders.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Real-time stock tracking</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Low stock alerts</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Order-linked inventory</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Feature 5 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Client Communication</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Built-in messaging system for client communications. Send order updates and delivery reminders with ease.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Integrated messaging</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Automated reminders</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Status update notifications</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Feature 6 -->
                        <div class="bg-white dark:bg-zinc-800/50 p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 card-3d backdrop-blur-sm border border-orange-100 dark:border-orange-900/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-400 rounded-2xl flex items-center justify-center mb-6 transform -rotate-6 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 font-playfair">Finance & Invoicing</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">Generate professional invoices, track payments, and monitor expenses. View financial reports at a glance.</p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Professional invoice generation</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Payment tracking</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Financial reporting</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Parallax Testimonial Section -->
            <section class="parallax-section py-24" style="background-image: url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80');">
                <div class="parallax-overlay"></div>
                <div class="parallax-content max-w-7xl mx-auto px-4 text-center">
                    <h2 class="text-4xl md:text-5xl font-bold mb-12 text-white font-playfair">What Our Users Say</h2>

                    <div class="grid md:grid-cols-3 gap-8">
                        <!-- Testimonial 1 -->
                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl border border-white/20 shadow-xl">
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                </svg>
                            </div>
                            <p class="text-white mb-6 italic">TailorSync has completely transformed how I manage my tailoring business. The client measurement system alone has saved me countless hours of work.</p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-xl mr-3">
                                    S
                                </div>
                                <div>
                                    <h4 class="text-white font-medium">Sarah Johnson</h4>
                                    <p class="text-orange-300 text-sm">Custom Bridal Designer</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 2 -->
                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl border border-white/20 shadow-xl">
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                </svg>
                            </div>
                            <p class="text-white mb-6 italic">The inventory management feature has been a game-changer for my shop. I always know exactly what materials I have on hand and what I need to order.</p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-xl mr-3">
                                    M
                                </div>
                                <div>
                                    <h4 class="text-white font-medium">Michael Chen</h4>
                                    <p class="text-orange-300 text-sm">Bespoke Menswear Tailor</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 3 -->
                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl border border-white/20 shadow-xl">
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                </svg>
                            </div>
                            <p class="text-white mb-6 italic">As someone who manages multiple tailors, the order tracking system has made it so much easier to keep everyone on the same page and meet our deadlines.</p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-xl mr-3">
                                    A
                                </div>
                                <div>
                                    <h4 class="text-white font-medium">Aisha Patel</h4>
                                    <p class="text-orange-300 text-sm">Fashion Studio Owner</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Workflow Section with Animated Steps -->
            <section class="py-20 bg-white dark:bg-zinc-900">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-bold mb-4 font-playfair gradient-text">Streamlined Workflow</h2>
                        <p class="text-xl text-zinc-700 dark:text-zinc-300 max-w-3xl mx-auto">
                            A simple, intuitive process designed to make your tailoring business more efficient
                        </p>
                    </div>

                    <div class="relative">
                        <!-- Timeline -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-gradient-to-b from-orange-400 to-amber-500"></div>

                        <!-- Steps -->
                        <div class="relative z-10">
                            <!-- Step 1 -->
                            <div class="flex flex-col md:flex-row items-center mb-16 fade-in-up">
                                <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl border border-orange-100 dark:border-orange-900/20 card-3d">
                                        <h3 class="text-2xl font-semibold mb-3 font-playfair text-orange-600 dark:text-orange-500">Client Registration</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">Add new clients and record their measurements in the system with our detailed measurement forms.</p>
                                        <div class="flex items-center text-orange-600 dark:text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Step 1</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-orange-600 to-amber-500 rounded-full h-12 w-12 flex items-center justify-center z-10 shadow-lg">
                                    <span class="text-white font-bold text-xl">1</span>
                                </div>
                                <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex flex-col md:flex-row items-center mb-16 fade-in-up">
                                <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                                <div class="bg-gradient-to-r from-orange-600 to-amber-500 rounded-full h-12 w-12 flex items-center justify-center z-10 shadow-lg">
                                    <span class="text-white font-bold text-xl">2</span>
                                </div>
                                <div class="md:w-1/2 md:pl-12 md:text-left mb-6 md:mb-0">
                                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl border border-orange-100 dark:border-orange-900/20 card-3d">
                                        <h3 class="text-2xl font-semibold mb-3 font-playfair text-orange-600 dark:text-orange-500">Design Creation</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">Upload design sketches and tag fabrics and styles for reference in our visual design gallery.</p>
                                        <div class="flex items-center text-orange-600 dark:text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Step 2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex flex-col md:flex-row items-center mb-16 fade-in-up">
                                <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl border border-orange-100 dark:border-orange-900/20 card-3d">
                                        <h3 class="text-2xl font-semibold mb-3 font-playfair text-orange-600 dark:text-orange-500">Order Management</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">Create orders with client details, design specifications, and due dates using our Kanban-style board.</p>
                                        <div class="flex items-center text-orange-600 dark:text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Step 3</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-orange-600 to-amber-500 rounded-full h-12 w-12 flex items-center justify-center z-10 shadow-lg">
                                    <span class="text-white font-bold text-xl">3</span>
                                </div>
                                <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                            </div>

                            <!-- Step 4 -->
                            <div class="flex flex-col md:flex-row items-center mb-16 fade-in-up">
                                <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                                <div class="bg-gradient-to-r from-orange-600 to-amber-500 rounded-full h-12 w-12 flex items-center justify-center z-10 shadow-lg">
                                    <span class="text-white font-bold text-xl">4</span>
                                </div>
                                <div class="md:w-1/2 md:pl-12 md:text-left mb-6 md:mb-0">
                                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl border border-orange-100 dark:border-orange-900/20 card-3d">
                                        <h3 class="text-2xl font-semibold mb-3 font-playfair text-orange-600 dark:text-orange-500">Production Tracking</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">Track order progress through cutting, sewing, fitting, and delivery stages with real-time updates.</p>
                                        <div class="flex items-center text-orange-600 dark:text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Step 4</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5 -->
                            <div class="flex flex-col md:flex-row items-center fade-in-up">
                                <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl border border-orange-100 dark:border-orange-900/20 card-3d">
                                        <h3 class="text-2xl font-semibold mb-3 font-playfair text-orange-600 dark:text-orange-500">Invoicing & Payment</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">Generate professional invoices, record payments, and track financial performance with detailed reports.</p>
                                        <div class="flex items-center text-orange-600 dark:text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Step 5</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-orange-600 to-amber-500 rounded-full h-12 w-12 flex items-center justify-center z-10 shadow-lg">
                                    <span class="text-white font-bold text-xl">5</span>
                                </div>
                                <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Parallax CTA Section -->
            <section class="parallax-section py-24" style="background-image: url('https://images.unsplash.com/photo-1605289982774-9a6fef564df8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80');">
                <div class="parallax-overlay bg-gradient-to-b from-black/50 to-orange-900/70"></div>
                <div class="parallax-content max-w-4xl mx-auto px-4 text-center">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white font-playfair">Ready to Transform Your Tailoring Business?</h2>
                    <p class="text-xl mb-10 text-white/90 max-w-3xl mx-auto">
                        Join thousands of tailors and fashion designers who have streamlined their business with TailorSync. Experience the difference a dedicated management system can make.
                    </p>
                    <div class="flex flex-wrap justify-center gap-6">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-10 py-4 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white rounded-lg font-medium transition-colors text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 glow">
                                Start Your Free Trial
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="px-10 py-4 bg-white/10 backdrop-blur-md border-2 border-white/30 text-white hover:bg-white/20 rounded-lg font-medium transition-colors text-lg">
                                Log In
                            </a>
                        @endif
                    </div>
                    <div class="mt-12 bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/20 max-w-2xl mx-auto">
                        <div class="flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-white font-medium">Secure & Reliable</span>
                        </div>
                        <p class="text-white/80 text-sm">
                            Your data is securely stored and backed up regularly. We use industry-standard encryption to protect your information.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-gradient-to-b from-zinc-100 to-white dark:from-zinc-900 dark:to-zinc-800 py-16 mt-20">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12">
                    <div class="col-span-1 lg:col-span-1">
                        <div class="text-3xl font-bold gradient-text font-playfair mb-4">TailorSync</div>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">The complete management system for modern tailors and fashion designers.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-orange-600 hover:text-orange-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-orange-600 hover:text-orange-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-orange-600 hover:text-orange-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-zinc-800 dark:text-zinc-200">Features</h3>
                        <ul class="space-y-3 text-zinc-600 dark:text-zinc-400">
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Client Management</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Order Tracking</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Design Board</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Inventory Management</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Finance & Invoicing</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-zinc-800 dark:text-zinc-200">Resources</h3>
                        <ul class="space-y-3 text-zinc-600 dark:text-zinc-400">
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Documentation</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">API Reference</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Tutorials</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Support</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-zinc-800 dark:text-zinc-200">Company</h3>
                        <ul class="space-y-3 text-zinc-600 dark:text-zinc-400">
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-orange-600 dark:hover:text-orange-500 transition-colors">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-zinc-200 dark:border-zinc-800 mt-12 pt-8 text-center text-zinc-500 dark:text-zinc-400">
                    <p>&copy; {{ date('Y') }} TailorSync. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- JavaScript for animations -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Function to check if an element is in viewport
                function isInViewport(element) {
                    const rect = element.getBoundingClientRect();
                    return (
                        rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                        rect.bottom >= 0
                    );
                }

                // Function to handle fade-in animations
                function handleFadeInAnimations() {
                    const fadeElements = document.querySelectorAll('.fade-in-up');

                    fadeElements.forEach(element => {
                        if (isInViewport(element) && !element.classList.contains('visible')) {
                            element.classList.add('visible');
                        }
                    });
                }

                // Initial check for elements in viewport
                handleFadeInAnimations();

                // Listen for scroll events
                window.addEventListener('scroll', handleFadeInAnimations);

                // Also trigger on window resize
                window.addEventListener('resize', handleFadeInAnimations);
            });
        </script>
    </body>
</html>
