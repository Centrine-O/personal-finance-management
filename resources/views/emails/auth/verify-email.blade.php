{{--
Email Verification Template
=========================

This Blade template creates the HTML email that users receive when they
register for our personal finance application. The design focuses on:

- Security and trust (important for financial apps)
- Clear call-to-action
- Professional appearance
- Mobile-responsive design
- Security warnings and best practices

The template uses inline CSS for maximum email client compatibility
since many email clients strip out <style> tags and external stylesheets.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Verify Your Email Address</title>
    
    {{-- Inline styles for maximum email client compatibility --}}
    <style>
        /* Reset styles for consistent rendering across email clients */
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
        
        /* Header styles */
        .email-header {
            background-color: #2563eb;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        
        /* Content styles */
        .email-content {
            padding: 40px 30px;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-content h2 {
            color: #2563eb;
            font-size: 22px;
            margin-bottom: 20px;
        }
        
        .email-content p {
            margin-bottom: 16px;
            font-size: 16px;
        }
        
        /* Button styles */
        .verify-button {
            display: inline-block;
            background-color: #16a34a;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        
        .verify-button:hover {
            background-color: #15803d;
        }
        
        /* Security warning box */
        .security-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .security-warning h3 {
            color: #92400e;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .security-warning p {
            color: #92400e;
            margin: 0;
            font-size: 14px;
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
        
        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            
            .verify-button {
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
        
        {{-- Email Header with App Name --}}
        <div class="email-header">
            <h1>{{ $appName }}</h1>
        </div>
        
        {{-- Main Email Content --}}
        <div class="email-content">
            <h2>Verify Your Email Address</h2>
            
            <p>Hi {{ $user->first_name }},</p>
            
            <p>
                Welcome to {{ $appName }}! We're excited to help you take control of your personal finances 
                and achieve your financial goals.
            </p>
            
            <p>
                To complete your registration and secure your account, please verify your email address 
                by clicking the button below:
            </p>
            
            {{-- Main Call-to-Action Button --}}
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    Verify Email Address
                </a>
            </div>
            
            {{-- Security Warning Box --}}
            <div class="security-warning">
                <h3>🔒 Security Notice</h3>
                <p>
                    This verification link is valid for 24 hours and can only be used once. 
                    Never share this email or click on verification links from suspicious sources.
                </p>
            </div>
            
            <p>
                <strong>Why do we verify email addresses?</strong><br>
                Email verification helps us:
            </p>
            
            <ul style="margin: 10px 0 20px 20px;">
                <li>Protect your financial data with secure communications</li>
                <li>Send you important account security notifications</li>
                <li>Ensure you receive budget alerts and bill reminders</li>
                <li>Provide a way to recover your account if needed</li>
            </ul>
            
            <p>
                <strong>Having trouble with the button?</strong><br>
                Copy and paste this link into your browser:
            </p>
            
            <p>
                <a href="{{ $verificationUrl }}" class="alternative-link">{{ $verificationUrl }}</a>
            </p>
            
            <p>
                If you didn't create an account with {{ $appName }}, please ignore this email. 
                Your email address will not be added to our system.
            </p>
            
            <p>
                Questions or concerns? Contact our support team at 
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
            </p>
            
            <p>
                Best regards,<br>
                The {{ $appName }} Team
            </p>
        </div>
        
        {{-- Email Footer --}}
        <div class="email-footer">
            <p><strong>{{ $appName }}</strong></p>
            <p>Your Personal Finance Management Solution</p>
            <p>
                This is an automated message. Please do not reply to this email.<br>
                For support, contact us at {{ $supportEmail }}
            </p>
            <p style="margin-top: 20px; font-size: 12px;">
                © {{ date('Y') }} {{ $appName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>