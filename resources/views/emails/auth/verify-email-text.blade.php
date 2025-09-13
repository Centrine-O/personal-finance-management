{{--
Plain Text Email Verification Template
====================================

This is the plain text version of our email verification email.
Some email clients prefer or only support plain text, so we provide
this version to ensure all users can verify their accounts.

Plain text emails are also:
- Faster to load
- More accessible for screen readers
- Less likely to be marked as spam
- Universal compatibility across all email clients
--}}

{{ $appName }} - Verify Your Email Address

Hi {{ $user->first_name }},

Welcome to {{ $appName }}! We're excited to help you take control of your personal finances and achieve your financial goals.

To complete your registration and secure your account, please verify your email address by visiting the following link:

{{ $verificationUrl }}

SECURITY NOTICE:
This verification link is valid for 24 hours and can only be used once. Never share this email or click on verification links from suspicious sources.

Why do we verify email addresses?
Email verification helps us:
- Protect your financial data with secure communications
- Send you important account security notifications  
- Ensure you receive budget alerts and bill reminders
- Provide a way to recover your account if needed

If you didn't create an account with {{ $appName }}, please ignore this email. Your email address will not be added to our system.

Questions or concerns? Contact our support team at {{ config('mail.support_address', 'support@' . config('app.domain')) }}

Best regards,
The {{ $appName }} Team

---
{{ $appName }}
Your Personal Finance Management Solution

This is an automated message. Please do not reply to this email.
For support, contact us at {{ config('mail.support_address', 'support@' . config('app.domain')) }}

© {{ date('Y') }} {{ $appName }}. All rights reserved.