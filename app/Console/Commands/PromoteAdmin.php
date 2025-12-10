<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PromoteAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promote-admin {email : The email of the user to promote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote an existing user to admin';

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

        if ($user->role === 'admin') {
            $this->warn("User '{$user->name}' is already an admin.");
            return 0;
        }

        $this->info("User Found:");
        $this->table(
            ['Name', 'Email', 'Role'],
            [[$user->name, $user->email, $user->role]]
        );

        if ($this->confirm("Are you sure you want to promote '{$user->name}' to admin?")) {
            $user->role = 'admin';
            $user->can_create_resident = true;
            $user->save();

            $this->info("User '{$user->name}' promoted to admin successfully!");
        } else {
            $this->info("Operation cancelled.");
        }

        return 0;
    }
}
