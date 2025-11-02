<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Public welcome page
Route::get('/', function () {
    return view('welcome');
});

// Route to run Artisan commands programmatically
Route::get('/artisan/{type}', function ($type) {

    // Whitelisted commands
    $allowedCommands = [
        'storage-link'   => 'storage:link',
        'optimize'       => 'optimize',
        'optimize-clear' => 'optimize:clear',
    ];

    if (!array_key_exists($type, $allowedCommands)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid Artisan command type!'
        ], 400);
    }

    try {
        // Run the command programmatically
        Artisan::call($allowedCommands[$type]);

        return response()->json([
            'success' => true,
            'command' => $allowedCommands[$type],
            'output'  => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});
