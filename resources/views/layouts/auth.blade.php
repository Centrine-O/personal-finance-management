{{--
Authentication Layout
====================

This is a simplified layout specifically for authentication pages (login, register, password reset).
It provides a clean, focused interface without navigation distractions.

Key Features:
- Minimal design focused on the authentication task
- Professional appearance that builds trust
- Security-focused messaging
- Responsive design for all device sizes
- Consistent branding with main app

Why we use a separate auth layout:
- Authentication pages need different UX than main app pages
- Removes navigation distractions during sign-up/sign-in flow
- Allows for specific security messaging
- Better conversion rates with focused design
- Consistent auth experience across all auth pages
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Basic Meta Tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO and Security Meta Tags --}}
    <title>@yield('title', 'Authentication') - {{ config('app.name', 'Personal Finance Manager') }}</title>
    <meta name="description" content="@yield('description', 'Secure access to your personal finance management account.')">
    <meta name="robots" content="noindex, nofollow"> {{-- Don't index auth pages --}}
    
    {{-- Security Headers --}}
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind Configuration - same as main app for consistency
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'finance-blue': {
                            50: '#eff6ff',
                            100: '#dbeafe', 
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        'finance-green': {
                            50: '#f0fdf4',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        'finance-red': {
                            50: '#fef2f2',
                            500: '#ef4444',
                            600: '#dc2626',
                        },
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
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>
    
    {{-- Alpine.js for interactive components --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Custom Styles --}}
    <style>
        /* Smooth transitions */
        * {
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }
        
        /* Focus styles for accessibility */
        .focus-ring:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Background gradient for visual appeal */
        .auth-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Card shadow for depth */
        .auth-card {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
    
    @stack('head')
</head>

<body class="font-sans antialiased">
    {{-- Main Authentication Container --}}
    <div class="min-h-screen flex flex-col justify-center bg-finance-gray-50 py-12 sm:px-6 lg:px-8">
        
        {{-- Header with Logo and Brand --}}
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            {{-- Logo --}}
            <div class="flex justify-center">
                <div class="h-12 w-12 bg-finance-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            
            {{-- Brand Name --}}
            <h1 class="mt-4 text-center text-2xl font-bold text-finance-gray-900">
                {{ config('app.name', 'Personal Finance Manager') }}
            </h1>
            
            {{-- Page Subtitle --}}
            <h2 class="mt-2 text-center text-xl font-semibold text-finance-gray-700">
                @yield('subtitle', 'Secure Account Access')
            </h2>
            
            {{-- Security Badge --}}
            <div class="mt-4 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-finance-green-100 text-finance-green-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    Bank-Level Security
                </span>
            </div>
        </div>
        
        {{-- Main Content Card --}}
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10 auth-card">
                
                {{-- Flash Messages --}}
                @if($errors->any())
                    <div class="mb-6 bg-finance-red-50 border border-finance-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-finance-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-finance-red-800">
                                    {{ $errors->count() > 1 ? 'There were some problems with your input:' : 'There was a problem with your input:' }}
                                </h3>
                                <div class="mt-2 text-sm text-finance-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="mb-6 bg-finance-green-50 border border-finance-green-200 rounded-md p-4">
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
                
                @if(session('warning'))
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
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
                    <div class="mb-6 bg-finance-blue-50 border border-finance-blue-200 rounded-md p-4">
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
                @endif
                
                {{-- Main Content --}}
                @yield('content')
            </div>
        </div>
        
        {{-- Footer Links --}}
        <div class="mt-8 text-center">
            <div class="flex justify-center space-x-6 text-sm">
                <a href="{{ route('help') }}" class="text-finance-gray-500 hover:text-finance-blue-600">
                    Help Center
                </a>
                <a href="{{ route('contact') }}" class="text-finance-gray-500 hover:text-finance-blue-600">
                    Contact Support
                </a>
                <a href="{{ route('privacy') }}" class="text-finance-gray-500 hover:text-finance-blue-600">
                    Privacy Policy
                </a>
            </div>
            
            {{-- Security Assurance --}}
            <div class="mt-4 text-xs text-finance-gray-500">
                <p>🔒 Your data is protected with enterprise-grade encryption</p>
                <p class="mt-1">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    {{-- JavaScript --}}
    <script>
        // CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[class*="bg-finance-"][class*="-50"], [class*="bg-yellow-50"]');
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
        
        // Form enhancement - show loading state on submit
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        const originalText = submitButton.textContent;
                        submitButton.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        `;
                        
                        // Re-enable after 10 seconds in case of issues
                        setTimeout(function() {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                        }, 10000);
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>