<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email}';
    protected $description = 'Promove um utilizador a administrador';

    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('Utilizador não encontrado com esse email.');
            return 1;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("Utilizador '{$user->name}' ({$user->email}) promovido a administrador!");
        return 0;
    }
}
