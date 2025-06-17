<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestParentChildRelationship extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-parent-child-relationship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the parent-child relationship between users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing parent-child relationship between users...');

        // Create a parent user
        $parentUser = User::create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->info("Created parent user with ID: {$parentUser->id}");

        // Create a child user with parent_id set to the parent user's ID
        $childUser = User::create([
            'name' => 'Child User',
            'email' => 'child@example.com',
            'password' => Hash::make('password'),
            'role' => 'team_member',
            'parent_id' => $parentUser->id,
        ]);

        $this->info("Created child user with ID: {$childUser->id}");

        // Create a client for the parent user
        $parentClient = Client::create([
            'user_id' => $parentUser->id,
            'name' => 'Parent Client',
            'email' => 'parentclient@example.com',
        ]);

        $this->info("Created client for parent user with ID: {$parentClient->id}");

        // Create a client for the child user
        $childClient = Client::create([
            'user_id' => $childUser->id,
            'name' => 'Child Client',
            'email' => 'childclient@example.com',
        ]);

        $this->info("Created client for child user with ID: {$childClient->id}");

        // Test if the child user can access the parent user's client
        $childUserClients = $childUser->clients()->get();
        $this->info("Child user's own clients count: " . $childUserClients->count());

        $childUserAllClients = $childUser->allClients()->get();
        $this->info("Child user's all clients count (including parent's): " . $childUserAllClients->count());

        // Test if the parent user can access the child user's client
        $parentUserClients = $parentUser->clients()->get();
        $this->info("Parent user's own clients count: " . $parentUserClients->count());

        $parentUserAllClients = $parentUser->allClients()->get();
        $this->info("Parent user's all clients count: " . $parentUserAllClients->count());

        $this->info('Test completed.');
    }
}
