<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoteAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:demote-admin {email : The email of the user to demote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demote an admin user to standard user (krama)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }

        if ($user->role !== 'admin') {
            $this->warn("User '{$user->name}' is not an admin.");
            return 0;
        }

        $this->info("User Found:");
        $this->table(
            ['Name', 'Email', 'Role'],
            [[$user->name, $user->email, $user->role]]
        );

        if ($this->confirm("Are you sure you want to demote '{$user->name}' from admin to krama?")) {
            $user->role = 'krama';
            $user->can_create_resident = false;
            $user->save();

            $this->info("User '{$user->name}' demoted to krama successfully!");
        } else {
            $this->info("Operation cancelled.");
        }

        return 0;
    }
}
