{{--
Main Application Layout
======================

This is the master layout template for our Personal Finance Management application.
All pages will extend this layout to maintain consistent styling, navigation, and structure.

Key Features:
- Responsive design that works on desktop, tablet, and mobile
- Modern, professional styling appropriate for financial applications
- Security-focused user interface elements
- Accessibility features for screen readers
- SEO-friendly meta tags
- Integration with Alpine.js for interactive components
- Tailwind CSS for utility-first styling

Why we use layouts:
- Consistent look and feel across all pages
- Single place to update global elements (navigation, footer, etc.)
- Reduces code duplication
- Easy maintenance and updates
- Better user experience with familiar navigation
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Basic Meta Tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <title>@yield('title', config('app.name', 'Personal Finance Manager'))</title>
    <meta name="description" content="@yield('description', 'Take control of your finances with our comprehensive personal finance management system. Track expenses, manage budgets, and achieve your financial goals.')">
    <meta name="keywords" content="@yield('keywords', 'personal finance, budget tracker, expense management, financial goals, money management')">
    <meta name="author" content="{{ config('app.name') }}">
    
    {{-- Security Meta Tags --}}
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    {{-- Favicon and App Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    {{-- Fonts - Using Google Fonts for better typography --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    {{-- Tailwind CSS for styling --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind CSS Configuration
        tailwind.config = {
            theme: {
                extend: {
                    // Custom colors for our financial app
                    colors: {
                        // Primary brand colors
                        'finance-blue': {
                            50: '#eff6ff',
                            100: '#dbeafe', 
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        // Success green for positive amounts
                        'finance-green': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        },
                        // Warning red for expenses and alerts
                        'finance-red': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        },
                        // Neutral grays for text and backgrounds
                        'finance-gray': {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    },
                    // Custom font family
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    // Custom spacing for financial data display
                    spacing: {
                        '18': '4.5rem',
                        '88': '22rem',
                        '128': '32rem',
                    }
                }
            }
        }
    </script>
    
    {{-- Alpine.js for interactive components --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Chart.js for financial charts and graphs --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    {{-- Custom CSS for additional styling --}}
    <style>
        /* Custom scrollbar for better UX */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Smooth transitions for better UX */
        * {
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }
        
        /* Focus styles for accessibility */
        .focus-ring:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Custom utility classes */
        .text-money-positive { color: #16a34a; }
        .text-money-negative { color: #dc2626; }
        .bg-money-positive { background-color: #f0fdf4; }
        .bg-money-negative { background-color: #fef2f2; }
    </style>
    
    {{-- Additional head content from child templates --}}
    @stack('head')
</head>

<body class="bg-finance-gray-50 font-sans antialiased">
    {{-- Main Application Container --}}
    <div id="app" class="min-h-screen bg-finance-gray-50">
        
        {{-- Navigation Header --}}
        <nav class="bg-white shadow-sm border-b border-finance-gray-200" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    
                    {{-- Logo and Brand --}}
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            {{-- Logo Icon --}}
                            <div class="h-8 w-8 bg-finance-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            
                            {{-- Brand Name --}}
                            <h1 class="ml-3 text-xl font-bold text-finance-gray-900">
                                {{ config('app.name', 'Personal Finance') }}
                            </h1>
                        </div>
                        
                        {{-- Desktop Navigation Links --}}
                        @auth
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="{{ route('dashboard') }}" 
                               class="@if(request()->routeIs('dashboard')) border-finance-blue-500 text-finance-gray-900 @else border-transparent text-finance-gray-500 hover:border-finance-gray-300 hover:text-finance-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            
                            <a href="{{ route('accounts.index') }}" 
                               class="@if(request()->routeIs('accounts.*')) border-finance-blue-500 text-finance-gray-900 @else border-transparent text-finance-gray-500 hover:border-finance-gray-300 hover:text-finance-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Accounts
                            </a>
                            
                            <a href="{{ route('transactions.index') }}" 
                               class="@if(request()->routeIs('transactions.*')) border-finance-blue-500 text-finance-gray-900 @else border-transparent text-finance-gray-500 hover:border-finance-gray-300 hover:text-finance-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Transactions
                            </a>
                            
                            <a href="{{ route('budgets.index') }}" 
                               class="@if(request()->routeIs('budgets.*')) border-finance-blue-500 text-finance-gray-900 @else border-transparent text-finance-gray-500 hover:border-finance-gray-300 hover:text-finance-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Budgets
                            </a>
                            
                            <a href="{{ route('goals.index') }}" 
                               class="@if(request()->routeIs('goals.*')) border-finance-blue-500 text-finance-gray-900 @else border-transparent text-finance-gray-500 hover:border-finance-gray-300 hover:text-finance-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Goals
                            </a>
                        </div>
                        @endauth
                    </div>
                    
                    {{-- Right side navigation --}}
                    <div class="flex items-center space-x-4">
                        
                        @auth
                            {{-- User Account Dropdown --}}
                            <div class="relative" x-data="{ dropdownOpen: false }">
                                <button @click="dropdownOpen = !dropdownOpen" 
                                        class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                                    {{-- User Avatar --}}
                                    <div class="h-8 w-8 rounded-full bg-finance-blue-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ substr(Auth::user()->first_name, 0, 1) }}{{ substr(Auth::user()->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="ml-2 text-finance-gray-700">{{ Auth::user()->first_name }}</span>
                                    <svg class="ml-1 h-4 w-4 text-finance-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                {{-- Dropdown Menu --}}
                                <div x-show="dropdownOpen" 
                                     x-cloak
                                     @click.outside="dropdownOpen = false"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-finance-gray-200">
                                    
                                    <div class="px-4 py-2 border-b border-finance-gray-100">
                                        <p class="text-sm text-finance-gray-900 font-medium">{{ Auth::user()->full_name }}</p>
                                        <p class="text-xs text-finance-gray-500">{{ Auth::user()->email }}</p>
                                    </div>
                                    
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-finance-gray-700 hover:bg-finance-gray-50">
                                        Profile Settings
                                    </a>
                                    
                                    <a href="{{ route('profile.preferences') }}" class="block px-4 py-2 text-sm text-finance-gray-700 hover:bg-finance-gray-50">
                                        Preferences
                                    </a>
                                    
                                    <a href="{{ route('profile.security') }}" class="block px-4 py-2 text-sm text-finance-gray-700 hover:bg-finance-gray-50">
                                        Security
                                    </a>
                                    
                                    <div class="border-t border-finance-gray-100">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-finance-gray-700 hover:bg-finance-gray-50">
                                                Sign Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                        @else
                            {{-- Guest User Navigation --}}
                            <a href="{{ route('login') }}" 
                               class="text-finance-gray-500 hover:text-finance-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                                Sign In
                            </a>
                            
                            <a href="{{ route('register') }}" 
                               class="bg-finance-blue-600 hover:bg-finance-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium focus-ring">
                                Get Started
                            </a>
                        @endauth
                        
                        {{-- Mobile menu button --}}
                        <div class="md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                    class="text-finance-gray-400 hover:text-finance-gray-500 focus:outline-none focus:text-finance-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Mobile Navigation Menu --}}
            @auth
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-finance-gray-200 bg-white">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="@if(request()->routeIs('dashboard')) bg-finance-blue-50 border-finance-blue-500 text-finance-blue-700 @else text-finance-gray-500 hover:text-finance-gray-700 hover:bg-finance-gray-50 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Dashboard
                    </a>
                    
                    <a href="{{ route('accounts.index') }}" 
                       class="@if(request()->routeIs('accounts.*')) bg-finance-blue-50 border-finance-blue-500 text-finance-blue-700 @else text-finance-gray-500 hover:text-finance-gray-700 hover:bg-finance-gray-50 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Accounts
                    </a>
                    
                    <a href="{{ route('transactions.index') }}" 
                       class="@if(request()->routeIs('transactions.*')) bg-finance-blue-50 border-finance-blue-500 text-finance-blue-700 @else text-finance-gray-500 hover:text-finance-gray-700 hover:bg-finance-gray-50 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Transactions
                    </a>
                    
                    <a href="{{ route('budgets.index') }}" 
                       class="@if(request()->routeIs('budgets.*')) bg-finance-blue-50 border-finance-blue-500 text-finance-blue-700 @else text-finance-gray-500 hover:text-finance-gray-700 hover:bg-finance-gray-50 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Budgets
                    </a>
                    
                    <a href="{{ route('goals.index') }}" 
                       class="@if(request()->routeIs('goals.*')) bg-finance-blue-50 border-finance-blue-500 text-finance-blue-700 @else text-finance-gray-500 hover:text-finance-gray-700 hover:bg-finance-gray-50 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Goals
                    </a>
                </div>
            </div>
            @endif
        </nav>
        
        {{-- Flash Messages --}}
        @if(session('success') || session('error') || session('warning') || session('info'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="bg-finance-green-50 border border-finance-green-200 rounded-md p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-finance-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-finance-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-finance-red-50 border border-finance-red-200 rounded-md p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-finance-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-finance-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('info'))
                <div class="bg-finance-blue-50 border border-finance-blue-200 rounded-md p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-finance-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-finance-blue-700">{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif
        
        {{-- Main Content Area --}}
        <main class="flex-grow">
            @yield('content')
        </main>
        
        {{-- Footer --}}
        <footer class="bg-white border-t border-finance-gray-200 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    
                    {{-- Company Info --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <div class="h-8 w-8 bg-finance-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-bold text-finance-gray-900">{{ config('app.name') }}</h3>
                        </div>
                        <p class="text-finance-gray-600 text-sm leading-relaxed">
                            Take control of your financial future with our comprehensive personal finance management system. 
                            Track expenses, manage budgets, and achieve your financial goals with confidence.
                        </p>
                    </div>
                    
                    {{-- Quick Links --}}
                    <div>
                        <h4 class="text-finance-gray-900 font-semibold mb-3">Quick Links</h4>
                        <ul class="space-y-2">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Dashboard</a></li>
                                <li><a href="{{ route('accounts.index') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Accounts</a></li>
                                <li><a href="{{ route('transactions.index') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Transactions</a></li>
                                <li><a href="{{ route('budgets.index') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Budgets</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Sign In</a></li>
                                <li><a href="{{ route('register') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Get Started</a></li>
                            @endauth
                        </ul>
                    </div>
                    
                    {{-- Support Links --}}
                    <div>
                        <h4 class="text-finance-gray-900 font-semibold mb-3">Support</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('help') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Help Center</a></li>
                            <li><a href="{{ route('contact') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Contact Us</a></li>
                            <li><a href="{{ route('privacy') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Privacy Policy</a></li>
                            <li><a href="{{ route('terms') }}" class="text-finance-gray-600 hover:text-finance-blue-600 text-sm">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                
                {{-- Bottom Footer --}}
                <div class="border-t border-finance-gray-200 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-finance-gray-500 text-sm">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                    <div class="mt-4 md:mt-0 flex space-x-6">
                        <span class="text-finance-gray-500 text-xs">🔒 Bank-level Security</span>
                        <span class="text-finance-gray-500 text-xs">📱 Mobile Friendly</span>
                        <span class="text-finance-gray-500 text-xs">🏆 Award Winning</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    {{-- Additional JavaScript --}}
    <script>
        // CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[class*="bg-finance-"][class*="-50"]');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.transition = 'opacity 0.5s ease-out';
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
    
    {{-- Additional scripts from child templates --}}
    @stack('scripts')
</body>
</html>