<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $accounts = [
            ['role' => 'admin', 'name' => 'System Administrator', 'email' => 'admin@dasureco.local'],
            ['role' => 'supervisor', 'name' => 'Field Supervisor', 'email' => 'supervisor@dasureco.local'],
            ['role' => 'operations', 'name' => 'Operations Staff', 'email' => 'operations@dasureco.local'],
            ['role' => 'field', 'name' => 'Field Personnel', 'email' => 'field@dasureco.local'],
        ];
        $credentials = [];

        foreach ($accounts as $account) {
            $password = Str::random(32);
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'is_active' => true,
                    'password' => $password,
                ],
            );

            if ($user->wasRecentlyCreated) {
                $credentials[] = [$account['role'], $account['email'], $password];
            }
        }

        if ($credentials !== []) {
            $this->command?->table(['Role', 'Email', 'Password'], $credentials);
        }
    }
}
