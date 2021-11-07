<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Customer::factory(10)->create()->each(function ($customer) {
            $users = User::factory(10)->create()->each(function ($user) {
                $sessions = Session::factory(10)->make();
                $user->sessions()->saveMany($sessions);
            });

            $customer->users()->saveMany($users);
        });
    }
}
