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
            'first_name' => 'Jun',
            'last_name' => 'Magbanua',
            'position_designation' => 'Records Officer'
        ]);

        $division = Division::create([
            'description' => 'Technical'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
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
            'first_name' => 'Virginia',
            'last_name' => 'Akiate',
            'position_designation' => 'Regional Director IV'
        ]);

        $role = Role::create([
            'division_id' => $division->id,
            'description' => 'Chief Administrative Officer',
            'level' => 2
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'chedro4_cao',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'first_name' => 'Freddie',
            'last_name' => 'Bulauan',
            'position_designation' => 'Chief Administrative Officer'
        ]);
    }
}
