<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use App\Models\Profile;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $division = Division::create([
            'description' => 'Administrative'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Records Officer',
            'level' => 1
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'first_name' => 'Records',
            'last_name' => 'Officer',
            'position_designation' => 'Records Officer'
        ]);
    }
}
