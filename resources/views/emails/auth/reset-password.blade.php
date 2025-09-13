{{--
Password Reset Email Template
===========================

This template creates the HTML email sent when users request a password reset
for their personal finance account. Security is paramount for financial applications,
so this template includes:

- Clear security warnings
- Time-limited reset instructions
- Contact information for security concerns
- Professional, trustworthy design
- Clear action steps

The design uses inline CSS for maximum compatibility across email clients.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Reset Your Password</title>
    
    {{-- Inline styles for email client compatibility --}}
    <style>
        /* Email client reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }
        
        /* Base styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f4f4;
            font-family: Arial, Helvetica, sans-serif;
        }
        
        /* Container styles */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        /* Header styles - using red/orange for urgency */
        .email-header {
            background-color: #dc2626;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        
        .email-header .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        /* Content styles */
        .email-content {
            padding: 40px 30px;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-content h2 {
            color: #dc2626;
            font-size: 22px;
            margin-bottom: 20px;
        }
        
        .email-content p {
            margin-bottom: 16px;
            font-size: 16px;
        }
        
        /* Reset button styles - using blue for action */
        .reset-button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        
        .reset-button:hover {
            background-color: #1d4ed8;
        }
        
        /* Critical security warning box */
        .security-alert {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .security-alert h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .security-alert p {
            color: #991b1b;
            margin: 0 0 8px 0;
            font-size: 14px;
        }
        
        /* Expiration warning */
        .expiration-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .expiration-warning p {
            color: #92400e;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        /* Footer styles */
        .email-footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }
        
        /* Link styles */
        .alternative-link {
            color: #2563eb;
            text-decoration: underline;
            word-break: break-all;
            font-size: 14px;
        }
        
        /* Contact information highlighting */
        .contact-info {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .contact-info p {
            color: #0c4a6e;
            margin: 0;
            font-size: 14px;
        }
        
        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            
            .reset-button {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>
    {{-- Main email container --}}
    <div class="email-container">
        
        {{-- Email Header with Security Icon --}}
        <div class="email-header">
            <div class="icon">🔐</div>
            <h1>{{ $appName }}</h1>
        </div>
        
        {{-- Main Email Content --}}
        <div class="email-content">
            <h2>Password Reset Request</h2>
            
            <p>Hi {{ $user->first_name }},</p>
            
            <p>
                We received a request to reset the password for your {{ $appName }} account 
                associated with this email address.
            </p>
            
            {{-- Expiration Warning --}}
            <div class="expiration-warning">
                <p>⏰ This reset link expires in {{ $expireMinutes }} minutes</p>
            </div>
            
            <p>
                To reset your password, click the button below:
            </p>
            
            {{-- Main Reset Button --}}
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset My Password
                </a>
            </div>
            
            {{-- Critical Security Alert --}}
            <div class="security-alert">
                <h3>🚨 Security Alert</h3>
                <p><strong>If you did not request this password reset:</strong></p>
                <p>• Do NOT click the reset link above</p>
                <p>• Your account may be under attack</p>
                <p>• Contact our security team immediately at {{ $securityEmail }}</p>
                <p>• Consider changing your password from a secure device</p>
            </div>
            
            <p>
                <strong>Password Reset Security Tips:</strong>
            </p>
            
            <ul style="margin: 10px 0 20px 20px;">
                <li>Only reset your password from a device you trust</li>
                <li>Use a strong, unique password that you haven't used before</li>
                <li>Don't use personal information (names, birthdays, etc.)</li>
                <li>Consider using a password manager for better security</li>
                <li>Enable two-factor authentication after resetting</li>
            </ul>
            
            <p>
                <strong>Having trouble with the button?</strong><br>
                Copy and paste this link into your browser:
            </p>
            
            <p>
                <a href="{{ $resetUrl }}" class="alternative-link">{{ $resetUrl }}</a>
            </p>
            
            {{-- Contact Information Box --}}
            <div class="contact-info">
                <p>
                    <strong>Need Help?</strong><br>
                    Support: <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a><br>
                    Security Issues: <a href="mailto:{{ $securityEmail }}">{{ $securityEmail }}</a>
                </p>
            </div>
            
            <p>
                <strong>Important:</strong> This password reset request was made from 
                IP address {{ request()->ip() ?? 'unknown' }} at {{ now()->format('F j, Y \a\t g:i A T') }}.
            </p>
            
            <p>
                Stay secure,<br>
                The {{ $appName }} Security Team
            </p>
        </div>
        
        {{-- Email Footer --}}
        <div class="email-footer">
            <p><strong>{{ $appName }}</strong></p>
            <p>Your Personal Finance Management Solution</p>
            <p>
                This is an automated security message. Please do not reply to this email.<br>
                For support, contact us at {{ $supportEmail }}
            </p>
            <p style="margin-top: 20px; font-size: 12px;">
                © {{ date('Y') }} {{ $appName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>