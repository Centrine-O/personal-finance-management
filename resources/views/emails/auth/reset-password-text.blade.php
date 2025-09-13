{{--
Plain Text Password Reset Email Template
======================================

This is the plain text version of our password reset email.
Plain text ensures compatibility with all email clients and
is often preferred for security-related communications.
--}}

{{ $appName }} - PASSWORD RESET REQUEST

Hi {{ $user->first_name }},

We received a request to reset the password for your {{ $appName }} account associated with this email address.

*** IMPORTANT: This reset link expires in {{ $expireMinutes }} minutes ***

To reset your password, visit the following link:
{{ $resetUrl }}

SECURITY ALERT:
If you did not request this password reset:
- Do NOT click the reset link above
- Your account may be under attack  
- Contact our security team immediately at {{ config('mail.security_address', 'security@' . config('app.domain')) }}
- Consider changing your password from a secure device

Password Reset Security Tips:
- Only reset your password from a device you trust
- Use a strong, unique password that you haven't used before
- Don't use personal information (names, birthdays, etc.)
- Consider using a password manager for better security
- Enable two-factor authentication after resetting

IMPORTANT: This password reset request was made from IP address {{ request()->ip() ?? 'unknown' }} at {{ now()->format('F j, Y \a\t g:i A T') }}.

Need Help?
Support: {{ config('mail.support_address', 'support@' . config('app.domain')) }}
Security Issues: {{ config('mail.security_address', 'security@' . config('app.domain')) }}

Stay secure,
The {{ $appName }} Security Team

---
{{ $appName }}
Your Personal Finance Management Solution

This is an automated security message. Please do not reply to this email.
For support, contact us at {{ config('mail.support_address', 'support@' . config('app.domain')) }}

© {{ date('Y') }} {{ $appName }}. All rights reserved.