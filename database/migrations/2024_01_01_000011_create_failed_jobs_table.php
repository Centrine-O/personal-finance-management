<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Failed Jobs Table Migration
 * 
 * This table stores information about background jobs that failed to complete.
 * Laravel uses this for error tracking and job retry functionality.
 * 
 * Examples of jobs in our finance app:
 * - Sending bill reminder emails
 * - Generating monthly budget reports
 * - Processing uploaded receipts
 * - Syncing bank account data
 * 
 * Why this structure?
 * - Track failed jobs for debugging
 * - Store exception details for troubleshooting
 * - Support job retry functionality
 * - Monitor system reliability
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Unique identifier for this job
            $table->string('uuid')->unique();
            
            // Which connection/queue this job was on
            $table->text('connection');
            $table->text('queue');
            
            // The job payload (serialized job data)
            $table->longText('payload');
            
            // The exception that caused the failure
            $table->longText('exception');
            
            // When this job failed
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};