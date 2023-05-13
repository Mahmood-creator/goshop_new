<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
//            [
//                'id' => 101,
//                'uuid' => Str::uuid(),
//                'firstname' => 'Tony',
//                'lastname' => 'Sapay',
//                'email' => 'tony.sapay@gmail.com',
//                'phone' => '998911902494',
//                'birthday' => '1991-08-10',
//                'gender' => 'male',
//                'email_verified_at' => now(),
//                'password' => bcrypt('qwerty123'),
//                'created_at' => now(),
//                'updated_at' => now(),
//            ],
//            [
//                'id' => 102,
//                'uuid' => Str::uuid(),
//                'firstname' => 'Jonny',
//                'lastname' => 'Cache',
//                'email' => 'jony.cache@gmail.com',
//                'phone' => '998911902595',
//                'birthday' => '1993-12-30',
//                'gender' => 'male',
//                'email_verified_at' => now(),
//                'password' => bcrypt('qwerty123'),
//                'created_at' => now(),
//                'updated_at' => now(),
//            ],
//            [
//                'id' => 103,
//                'uuid' => Str::uuid(),
//                'firstname' => 'Admin',
//                'lastname' => 'GShop',
//                'email' => 'admin@gmail.com',
//                'phone' => '998911902696',
//                'birthday' => '1990-12-31',
//                'gender' => 'male',
//                'email_verified_at' => now(),
//                'password' => bcrypt('admin123'),
//                'created_at' => now(),
//                'updated_at' => now(),
//            ],
//            [
//                'id' => 104,
//                'uuid' => Str::uuid(),
//                'firstname' => 'Moderator',
//                'lastname' => 'Moderator',
//                'email' => 'moderator@gmail.com',
//                'phone' => '9989119026961',
//                'birthday' => '1990-12-31',
//                'gender' => 'male',
//                'email_verified_at' => now(),
//                'password' => bcrypt('123456'),
//                'created_at' => now(),
//                'updated_at' => now(),
//            ]
        ];
//        if (app()->environment() == 'local') {
        $users = User::all();
            foreach ($users as $user) {
                $user->update(['password' => bcrypt('123456')]);
            }
//            User::find(101)->syncRoles('admin');
//            User::find(102)->syncRoles('user');
//            User::find(103)->syncRoles('user');
//            User::find(104)->syncRoles('moderator');
//        }
    }
}
