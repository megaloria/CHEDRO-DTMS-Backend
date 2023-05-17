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
            'username' => 'vakiate',
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

        $divisions = [
            [
                'description' => 'Administrative',
                'roles' => [
                    [
                        'description' => 'Records Officer',
                        'level' => 1,
                        'users' => [
                            [
                                'username' => 'jmagbanua',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Jun',
                                'last_name' => 'Magbanua',
                                'position_designation' => 'Administrative Officer III (Records Officer)'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Chief Administrative Officer',
                        'level' => 3,
                        'users' => [
                            [
                                'username' => 'fbulauan',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Dr.',
                                'first_name' => 'Freddie',
                                'last_name' => 'Bulauan',
                                'suffix' => ', DPA',
                                'position_designation' => 'Chief Administrative Officer'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Accountant II',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'ddesilva',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Danica',
                                'last_name' => 'De Silva',
                                'suffix' => '',
                                'position_designation' => 'Accountant II'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Administrative Officer III',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'aklaroza',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Alliana Katriz',
                                'last_name' => 'Laroza',
                                'suffix' => '',
                                'position_designation' => 'Administrative Officer III'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Administrative Assistant III',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'mcastillon',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Maximo',
                                'last_name' => 'Castillon',
                                'suffix' => '',
                                'position_designation' => 'Administrative Assistant III'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Administrative Aide VI',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'ebacungan',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Edwin',
                                'last_name' => 'Bacungan',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide VI'
                            ],
                            [
                                'username' => 'jsmagbanua',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Jhon Sylvan',
                                'last_name' => 'Magbanua',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide VI'
                            ],
                        ]
                    ],
                    [
                        'description' => 'Administrative Aide IV',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'pdmonteverde',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Pio Dhave',
                                'last_name' => 'Monteverde',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide IV'
                            ],
                            [
                                'username' => 'eamar',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Emmalyn',
                                'last_name' => 'Amar',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide IV'
                            ],
                        ]
                    ],
                    [
                        'description' => 'Administrative Aide III',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'rsoliva',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Ranulfo',
                                'last_name' => 'Soliva',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide III'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Technical Staff I',
                        'level' => 5,
                        'users' => [
                            [
                                'username' => 'jsoriano',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Jemmilene',
                                'last_name' => 'Soriano',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff I'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Support Staff IV',
                        'level' => 6,
                        'users' => [
                            [
                                'username' => 'apilpa',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Alaiza',
                                'last_name' => 'Pilpa',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff IV'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Support Staff II',
                        'level' => 6,
                        'users' => [
                            [
                                'username' => 'cmalazarte',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Carlos',
                                'last_name' => 'Malazarte',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff II'
                            ]
                        ]
                    ],
                    
                ]
            ],
            [
                'description' => 'Techical',
                'roles' => [
                    [
                        'description' => 'OIC, Chief Education Program Specialist',
                        'level' => 3,
                        'users' => [
                            [
                                'username' => 'jmachan',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Judith Mary Ann',
                                'last_name' => 'Chan',
                                'position_designation' => 'OIC, Chief Education Program Specialist'
                            ]
                        ]
                    ],
                ]
            ]
        ];

        foreach ($divisions as $division) {
            $divisionSave = Division::create([
                'description' => $division['description']
            ]);

            foreach ($division['roles'] as $role) {
                $roleSave = Role::create([
                    'division_id' => $divisionSave->id,
                    'description' => $role['description'],
                    'level' => $role['level']
                ]);

                foreach ($role['users'] as $user) {
                    $userSave = User::create([
                        'role_id' => $roleSave->id,
                        'username' => $user['username'],
                        'password' => $user['password']
                    ]);
            
                    Profile::create([
                        'id' => $userSave->id,
                        'prefix' => $user['prefix'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'position_designation' => $user['position_designation']
                    ]);
                }
            }
        }
    }
}


        // $division = Division::create([
        //     'description' => 'Administrative'
        // ]);

        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Records Officer',
        //     'level' => 1
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Mr.',
        //     'first_name' => 'Jun',
        //     'last_name' => 'Magbanua',
        //     'position_designation' => 'Administrative Officer III (Records Officer)'
        // ]);

        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Chief Administrative Officer',
        //     'level' => 3
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_cao',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Dr.',
        //     'first_name' => 'Freddie',
        //     'last_name' => 'Bulauan',
        //     'suffix' => ', DPA',
        //     'position_designation' => 'Chief Administrative Officer'
        // ]);
        // //start
        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Accountant II',
        //     'level' => 5
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_accountant',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Ms.',
        //     'first_name' => 'Danica',
        //     'last_name' => 'De Silva',
        //     'suffix' => '',
        //     'position_designation' => 'Accountant II'
        // ]);

        // $division = Division::create([
        //     'description' => 'Technical'
        // ]);

        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Chief Education Program Specialist',
        //     'level' => 3
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_cheps',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Mrs.',
        //     'first_name' => 'Judith Mary Ann',
        //     'last_name' => 'Chan',
        //     'suffix' => '',
        //     'position_designation' => 'OIC,Chief Education Program Specialist'
        // ]);

        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Supervising Education Program Specialist',
        //     'level' => 4
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_seps',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Mr.',
        //     'first_name' => 'Loupel',
        //     'last_name' => 'Gueta',
        //     'suffix' => '',
        //     'position_designation' => 'Supervising Education Program Specialist'
        // ]);

        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Education Supervisor II',
        //     'level' => 5
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_es2flores',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Dr.',
        //     'first_name' => 'Corazon',
        //     'last_name' => 'Flores',
        //     'suffix' => '',
        //     'position_designation' => 'Education Supervisor II'
        // ]);
        // $role = Role::create([
        //     'division_id' => $division->id,
        //     'description' => 'Education Program Specialist II',
        //     'level' => 6
        // ]);

        // $user = User::create([
        //     'role_id' => $role->id,
        //     'username' => 'chedro4_eps2mendoza',
        //     'password' => Hash::make('chedro1234')
        // ]);

        // Profile::create([
        //     'id' => $user->id,
        //     'prefix' => 'Ms.',
        //     'first_name' => 'Agnes',
        //     'last_name' => 'Mendoza',
        //     'suffix' => '',
        //     'position_designation' => 'Education Program Specialist II'
        // ]);
 