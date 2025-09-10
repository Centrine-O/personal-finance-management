<?php

/*
|--------------------------------------------------------------------------
| Laravel Application Entry Point
|--------------------------------------------------------------------------
|
| This file is the entry point for all HTTP requests to our application.
| Every time someone visits our website or calls our API, this file runs.
|
| It bootstraps Laravel and handles the incoming request.
| Think of this as the "front door" of our application.
|
*/

use Illuminate\Http\Request;

// Define the start time for performance monitoring
// This helps us measure how long each request takes
define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the full application framework.
|
*/

// Check if maintenance mode file exists
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

// Load Composer's autoloader
// This allows us to use all our PHP classes without manually including files
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

// Bootstrap the Laravel application
// This creates our application instance with all its configuration
$app = require_once __DIR__.'/../bootstrap/app.php';

// Create the HTTP kernel
// The kernel handles HTTP requests and responses
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle the incoming request
// This is where the magic happens:
// 1. Laravel receives the request
// 2. Routes it to the correct controller
// 3. Processes the business logic
// 4. Returns a response
$response = $kernel->handle(
    $request = Request::capture()
);

// Send the response to the browser
// This outputs the HTML, JSON, or whatever response type we're sending
$response->send();

// Perform any cleanup tasks
// This handles things like closing database connections, cleaning up memory, etc.
$kernel->terminate($request, $response);