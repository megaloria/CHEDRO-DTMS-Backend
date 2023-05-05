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

        $role = Role::create([
            'description' => 'Regional Director IV',
            'level' => 2
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_rd',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Dr.',
            'first_name' => 'Virginia',
            'last_name' => 'Akiate',
            'suffix' => ', CESO III',
            'position_designation' => 'Regional Director IV'
        ]);

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
            'prefix' => 'Mr.',
            'first_name' => 'Jun',
            'last_name' => 'Magbanua',
            'position_designation' => 'Administrative Officer III (Records Officer)'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Chief Administrative Officer',
            'level' => 3
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_cao',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Dr.',
            'first_name' => 'Freddie',
            'last_name' => 'Bulauan',
            'suffix' => ', DPA',
            'position_designation' => 'Chief Administrative Officer'
        ]);
        //start
        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Accountant II',
            'level' => 5
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_accountant',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Ms.',
            'first_name' => 'Danica',
            'last_name' => 'De Silva',
            'suffix' => '',
            'position_designation' => 'Accountant II'
        ]);

        $division = Division::create([
            'description' => 'Technical'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Chief Education Program Specialist',
            'level' => 3
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_cheps',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Mrs.',
            'first_name' => 'Judith Mary Ann',
            'last_name' => 'Chan',
            'suffix' => '',
            'position_designation' => 'OIC,Chief Education Program Specialist'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Supervising Education Program Specialist',
            'level' => 4
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_seps',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Mr.',
            'first_name' => 'Loupel',
            'last_name' => 'Gueta',
            'suffix' => '',
            'position_designation' => 'Supervising Education Program Specialist'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Education Supervisor II',
            'level' => 5
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_es2flores',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Dr.',
            'first_name' => 'Corazon',
            'last_name' => 'Flores',
            'suffix' => '',
            'position_designation' => 'Education Supervisor II'
        ]);
    }
}
