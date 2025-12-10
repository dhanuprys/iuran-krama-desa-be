<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin 
                            {name : The name of the user} 
                            {email : The email of the user} 
                            {password : The password of the user} 
                            {--username= : The username of the user (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        // Default username to email prefix if not provided
        $username = $this->option('username') ?: explode('@', $email)[0];

        if (\App\Models\User::where('email', $email)->exists()) {
            $this->error('User with this email already exists!');
            return 1;
        }

        if (\App\Models\User::where('username', $username)->exists()) {
            $this->error('User with this username already exists!');
            return 1;
        }

        $user = \App\Models\User::create([
            'name' => $name,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'username' => $username,
            'role' => 'admin',
            'can_create_resident' => true,
        ]);

        $this->info("Admin user '{$user->name}' created successfully!");
        $this->table(
            ['Name', 'Email', 'Username', 'Role'],
            [[$user->name, $user->email, $user->username, $user->role]]
        );

        return 0;
    }
}
