<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ArtisanCommandController extends Controller
{
    public function run($command)
    {
        // Access: a logged-in user (normal /run route), OR a matching
        // DEPLOY_TOKEN query param (deploy route — used on shared hosting
        // with no CLI, where login itself may be broken by missing tables).
        // When no token is configured the deploy access simply 404s.
        $token = config('app.deploy_token');
        $tokenOk = !empty($token) && hash_equals($token, (string) request()->query('token'));

        if (!auth()->check() && !$tokenOk) {
            abort(404);
        }

        // Map the route parameters to actual Artisan commands.
        // --force: never prompt for confirmation in production.
        [$artisanCommand, $arguments] = match ($command) {
            'optimize'    => ['optimize:clear', []],       // Clears and caches config, routes, and views
            'route-clear' => ['route:clear', []],
            'migrate'     => ['migrate', ['--force' => true]],        // Runs outstanding migrations
            'migrate-status' => ['migrate:status', []],
            'migrate-rollback' => ['migrate:rollback', ['--step' => 1, '--force' => true]], // Rolls back the last batch of migrations
            'db-seed'     => ['db:seed', ['--class' => 'Database\Seeders\MasterSeeder', '--force' => true]], // Roles, permissions, super admin, branch
            default       => [null, []],
        };

        if (!$artisanCommand) {
            abort(404, "Invalid command execution attempt.");
        }

        try {
            // Run the command
            $exitCode = Artisan::call($artisanCommand, $arguments);

            // Capture the command line output text
            $output = Artisan::output();

            // Clean up ANSI escape characters and trailing dots/spaces
            $cleanOutput = preg_replace('/\.+\s+([0-9]+ms\s+DONE)/', ' -> $1', $output);
            $cleanOutput = preg_replace('/[^A-Za-z0-9 _.,\-:>()\n\r]/', '', $cleanOutput);

            // Trim empty spaces and normalize line breaks
            $cleanOutput = trim($cleanOutput);

            return response()->json([
                'status' => $exitCode === 0 ? 'success' : 'failed',
                'command' => $artisanCommand,
                'exit_code' => $exitCode,
                'output' => $cleanOutput, // No <br /> tags here, let the frontend handle lines
                'back' => route('home'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
