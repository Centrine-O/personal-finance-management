<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Custom Email Verification Notification
 * 
 * This class extends Laravel's default email verification notification
 * to provide a custom email template that fits our personal finance
 * application's branding and security requirements.
 * 
 * Key features:
 * - Custom email subject and content
 * - Security-focused messaging for financial application
 * - Queue support for better performance
 * - Custom styling and branding
 * 
 * Why we need this:
 * - Default Laravel email is generic and doesn't reflect our app's purpose
 * - Financial apps need clear, trustworthy email communications
 * - We want to include security warnings and best practices
 * - Custom design matches our application's look and feel
 */
class CustomVerifyEmail extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    /**
     * The queue connection that should be used to queue the notification.
     * 
     * Using 'emails' queue to separate email processing from other jobs
     * This allows us to prioritize and monitor email delivery separately
     */
    public $connection = 'redis';
    public $queue = 'emails';

    /**
     * Build the mail representation of the notification.
     * 
     * This method creates the actual email message that users will receive
     * when they register for our personal finance application.
     * 
     * @param mixed $notifiable The user who will receive the email
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // Generate the verification URL using Laravel's built-in method
        // This creates a secure, signed URL that expires after a set time
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            // Set a clear, professional subject line
            ->subject('Verify Your Email Address - ' . config('app.name'))
            
            // Set a custom view template (we'll create this next)
            ->view('emails.auth.verify-email', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'appName' => config('app.name'),
                'supportEmail' => config('mail.support_address', 'support@' . config('app.domain')),
            ])
            
            // Add a plain text version for better compatibility
            ->text('emails.auth.verify-email-text', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'appName' => config('app.name'),
            ]);
    }

    /**
     * Get the notification's delivery channels.
     * 
     * For email verification, we only use the mail channel
     * 
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Determine the time at which the job should timeout.
     * 
     * Email notifications should be processed quickly
     * 30 seconds is plenty of time for email generation
     * 
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * 
     * If email sending fails, we'll retry with exponential backoff
     * 
     * @param int $attempt
     * @return int
     */
    public function backoff(): array
    {
        return [1, 5, 10]; // Retry after 1s, then 5s, then 10s
    }

    /**
     * Handle a job failure.
     * 
     * If email verification fails to send, we need to log this
     * for security and user experience monitoring
     * 
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure for monitoring
        \Illuminate\Support\Facades\Log::error('Email verification notification failed', [
            'user_id' => $this->id ?? 'unknown',
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally, we could notify administrators
        // or trigger alternative verification methods
    }
}