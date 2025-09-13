<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Registration Request Validation
 * 
 * This form request class handles all validation for user registration.
 * Using dedicated request classes keeps our controllers clean and allows
 * for reusable validation logic across web and API endpoints.
 * 
 * Benefits:
 * - Centralized validation rules
 * - Automatic error handling
 * - Custom error messages
 * - Authorization logic
 * - Clean controller methods
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * For registration, we only allow guests (non-authenticated users)
     */
    public function authorize(): bool
    {
        // Only guest users can register
        return !$this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * These rules ensure we get clean, secure data for creating new users
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'first_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                // Only letters, spaces, hyphens, apostrophes, and periods
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            
            'last_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            
            // Email with strict validation for financial security
            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Strict email validation with DNS checking
                'max:255',
                'unique:users,email', // Must be unique
                // Optional: block disposable email services
                // 'not_regex:/^.+@(10minutemail|tempmail|guerrillamail)\./',
            ],
            
            // Strong password requirements for financial data protection
            'password' => [
                'required',
                'confirmed', // Must have matching password_confirmation field
                Password::min(8) // Minimum 8 characters
                    ->letters() // Must contain letters
                    ->mixedCase() // Must have both upper and lower case
                    ->numbers() // Must contain numbers
                    ->symbols() // Must contain special characters
                    ->uncompromised(), // Check against known breached passwords
            ],
            
            // Optional personal information
            'phone' => [
                'nullable',
                'string',
                'max:20',
                // International phone number format
                'regex:/^[\+]?[1-9][\d\s\-\(\)]{7,19}$/',
            ],
            
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today', // Must be in the past
                'after:' . now()->subYears(120)->toDateString(), // Reasonable age limit
            ],
            
            // Financial preferences
            'preferred_currency' => [
                'nullable',
                'string',
                'size:3', // Must be exactly 3 characters
                'regex:/^[A-Z]{3}$/', // Must be uppercase letters (USD, EUR, etc.)
                'in:USD,EUR,GBP,CAD,AUD,JPY,CHF,CNY,INR,KRW', // Supported currencies
            ],
            
            'timezone' => [
                'nullable',
                'string',
                'timezone', // Must be valid timezone identifier
            ],
            
            // Monthly income (optional, helps with budgeting suggestions)
            'monthly_income' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99', // Reasonable maximum
                'decimal:0,2', // Max 2 decimal places
            ],
            
            // Notification preferences
            'budget_alerts_enabled' => [
                'nullable',
                'boolean',
            ],
            
            'bill_reminders_enabled' => [
                'nullable',
                'boolean',
            ],
            
            'bill_reminder_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:30', // Reasonable reminder window
            ],
            
            // Legal agreements (required for compliance)
            'terms_accepted' => [
                'required',
                'accepted', // Must be true, 1, 'yes', or 'on'
            ],
            
            'privacy_accepted' => [
                'required',
                'accepted',
            ],
            
            // Marketing consent (optional)
            'marketing_emails' => [
                'nullable',
                'boolean',
            ],
            
            // Referral information (optional)
            'referral_code' => [
                'nullable',
                'string',
                'max:20',
                'alpha_num', // Only letters and numbers
                'exists:referral_codes,code', // Must be valid referral code
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     * 
     * These messages provide clear, user-friendly feedback
     */
    public function messages(): array
    {
        return [
            // Name validation messages
            'first_name.required' => 'Please enter your first name.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            
            'last_name.required' => 'Please enter your last name.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            
            // Email validation messages
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please use a different email or try logging in.',
            
            // Password validation messages
            'password.confirmed' => 'The password confirmation does not match.',
            
            // Phone validation messages
            'phone.regex' => 'Please enter a valid phone number.',
            
            // Date validation messages
            'date_of_birth.date' => 'Please enter a valid date of birth.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Please enter a realistic date of birth.',
            
            // Currency validation messages
            'preferred_currency.size' => 'Currency code must be exactly 3 letters.',
            'preferred_currency.regex' => 'Currency code must be 3 uppercase letters (e.g., USD, EUR).',
            'preferred_currency.in' => 'Selected currency is not supported.',
            
            // Income validation messages
            'monthly_income.numeric' => 'Monthly income must be a valid number.',
            'monthly_income.min' => 'Monthly income cannot be negative.',
            'monthly_income.max' => 'Monthly income seems too high. Please contact support if this is correct.',
            
            // Legal agreement messages
            'terms_accepted.required' => 'You must accept the Terms of Service to create an account.',
            'terms_accepted.accepted' => 'You must accept the Terms of Service to create an account.',
            'privacy_accepted.required' => 'You must accept the Privacy Policy to create an account.',
            'privacy_accepted.accepted' => 'You must accept the Privacy Policy to create an account.',
            
            // Referral messages
            'referral_code.exists' => 'Invalid referral code. Please check the code and try again.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * 
     * These provide more user-friendly field names in error messages
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'password' => 'password',
            'phone' => 'phone number',
            'date_of_birth' => 'date of birth',
            'preferred_currency' => 'preferred currency',
            'monthly_income' => 'monthly income',
            'terms_accepted' => 'Terms of Service',
            'privacy_accepted' => 'Privacy Policy',
            'referral_code' => 'referral code',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * This method runs before validation and allows us to clean/format
     * the input data for consistency.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Normalize email to lowercase
            'email' => strtolower($this->email ?? ''),
            
            // Trim whitespace from names
            'first_name' => trim($this->first_name ?? ''),
            'last_name' => trim($this->last_name ?? ''),
            
            // Normalize phone number (remove non-numeric characters except + at start)
            'phone' => $this->phone ? preg_replace('/[^\+\d]/', '', $this->phone) : null,
            
            // Ensure currency is uppercase
            'preferred_currency' => strtoupper($this->preferred_currency ?? ''),
            
            // Set default timezone if not provided
            'timezone' => $this->timezone ?? config('app.timezone', 'UTC'),
            
            // Convert string booleans to actual booleans
            'terms_accepted' => filter_var($this->terms_accepted ?? false, FILTER_VALIDATE_BOOLEAN),
            'privacy_accepted' => filter_var($this->privacy_accepted ?? false, FILTER_VALIDATE_BOOLEAN),
            'marketing_emails' => filter_var($this->marketing_emails ?? false, FILTER_VALIDATE_BOOLEAN),
            'budget_alerts_enabled' => filter_var($this->budget_alerts_enabled ?? true, FILTER_VALIDATE_BOOLEAN),
            'bill_reminders_enabled' => filter_var($this->bill_reminders_enabled ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     * 
     * This method is called when validation fails and allows us to
     * customize the response or perform additional actions.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log registration validation failures for security monitoring
        \Illuminate\Support\Facades\Log::info('Registration validation failed', [
            'email' => $this->email,
            'errors' => $validator->errors()->toArray(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);
        
        // Call parent method to handle the response
        parent::failedValidation($validator);
    }

    /**
     * Get data to be validated from the request.
     * 
     * This method allows us to customize which data gets validated.
     * We can exclude sensitive fields or add computed values.
     */
    public function validationData(): array
    {
        $data = parent::validationData();
        
        // Remove any fields we don't want to validate
        unset($data['password_confirmation']); // This is handled by 'confirmed' rule
        
        return $data;
    }

    /**
     * Get the validated data with additional processing.
     * 
     * This method returns the validated data after applying any
     * additional processing or default values.
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();
        
        // Apply defaults for optional fields
        $validated['timezone'] = $validated['timezone'] ?? config('app.timezone', 'UTC');
        $validated['preferred_currency'] = $validated['preferred_currency'] ?? 'USD';
        $validated['budget_alerts_enabled'] = $validated['budget_alerts_enabled'] ?? true;
        $validated['bill_reminders_enabled'] = $validated['bill_reminders_enabled'] ?? true;
        $validated['bill_reminder_days'] = $validated['bill_reminder_days'] ?? 3;
        
        // Remove fields that shouldn't be stored directly
        unset($validated['terms_accepted'], $validated['privacy_accepted']);
        
        return $validated;
    }
}