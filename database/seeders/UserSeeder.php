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
        //Regional Director
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
            'position_designation' => 'Regional Director IV',
            'email' => 'vakiate@ched.gov.ph'
        ]);

        //Administrator
        $role = Role::create([
            'description' => 'Record Officer',
            'level' => 1
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'jmagbanua_ro',
            'password' => Hash::make('chedro1234')
        ]);

        Profile::create([
            'id' => $user->id,
            'prefix' => 'Mr.',
            'first_name' => 'Jun',
            'last_name' => 'Magbanua',
            'suffix' => '',
            'position_designation' => 'Administrative Officer III',
            'email' => 'jmagbanua@ched.gov.ph'
        ]);

        $divisions = [
            [
                'description' => 'Administrative',
                'roles' => [
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
                                'position_designation' => 'Chief Administrative Officer',
                                'email' => 'fbulauan@ched.gov.ph'
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
                                'position_designation' => 'Accountant II',
                                'email' => 'ddesilva@ched.gov.ph'
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
                                'position_designation' => 'Administrative Officer III',
                                'email' => 'aklaroza@ched.gov.ph'
                            ],
                            [
                                'username' => 'jmagbanua',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Jun',
                                'last_name' => 'Magbanua',
                                'position_designation' => 'Administrative Officer III',
                                'email' => 'jmagbanua@ched.gov.ph'
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
                                'position_designation' => 'Administrative Assistant III',
                                'email' => 'mcastillon@ched.gov.ph'
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
                                'position_designation' => 'Administrative Aide VI',
                                'email' => 'ebacungan@ched.gov.ph'
                            ],
                            [
                                'username' => 'jsmagbanua',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Jhon Sylvan',
                                'last_name' => 'Magbanua',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide VI',
                                'email' => 'jsmagbanua@ched.gov.ph'
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
                                'position_designation' => 'Administrative Aide IV',
                                'email' => 'pdmonteverde@ched.gov.ph'
                            ],
                            [
                                'username' => 'eamar',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Emmalyn',
                                'last_name' => 'Amar',
                                'suffix' => '',
                                'position_designation' => 'Administrative Aide IV',
                                'email' => 'eamar@ched.gov.ph'
                            ],
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
                                'position_designation' => 'Project Technical Staff I',
                                'email' => 'jsoriano@ched.gov.ph'
                            ],
                            [
                                'username' => 'kcamacho',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Krystal',
                                'last_name' => 'Camacho',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff I',
                                'email' => 'kcamacho@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Support Staff IV',
                        'level' => 5,
                        'users' => [
                            [
                                'username' => 'lestilles',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Leah',
                                'last_name' => 'Estilles',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff IV',
                                'email' => 'lestilles@ched.gov.ph'
                            ],
                            [
                                'username' => 'icmaliksi',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Ian Christopher',
                                'last_name' => 'Maliksi',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff IV',
                                'email' => 'icmaliksi@ched.gov.ph'
                            ],
                            [
                                'username' => 'smarias',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Sheila Mae',
                                'last_name' => 'Arias',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff IV',
                                'email' => 'smarias@ched.gov.ph'
                            ],
                            [
                                'username' => 'japineda',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Joan Abegail',
                                'last_name' => 'Pineda',
                                'suffix' => '',
                                'position_designation' => 'Project Support Staff IV',
                                'email' => 'japineda@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Support Service',
                        'level' => 5,
                        'users' => [
                            [
                                'username' => 'apilpa',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Alaiza',
                                'last_name' => 'Pilpa',
                                'suffix' => '',
                                'position_designation' => 'Support Service',
                                'email' => 'apilpa@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Technical Staff II',
                        'level' => 5,
                        'users' => [
                            [
                                'username' => 'cmalazartejr',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Carlos',
                                'last_name' => 'Malazarte',
                                'suffix' => 'Jr.',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'cmalazartejr@ched.gov.ph'
                            ]
                        ]
                    ],

                ]
            ],
            [
                'description' => 'Technical',
                'roles' => [
                    [
                        'description' => 'Chief Education Program Specialist',
                        'level' => 3,
                        'users' => [
                            [
                                'username' => 'jmachan_ceps',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Dr.',
                                'first_name' => 'Judith Mary Ann',
                                'last_name' => 'Chan',
                                'suffix' => '',
                                'position_designation' => 'OIC, Chief Education Program Specialist',
                                'email' => 'jmachan@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Supervising Education Program Specialist',
                        'level' => 4,
                        'users' => [
                            [
                                'username' => 'lgueta_seps',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Loupel',
                                'last_name' => 'Gueta',
                                'suffix' => '',
                                'position_designation' => 'OIC, Supervising Education Program Specialist',
                                'email' => 'lgueta@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Education Supervisor II',
                        'level' => 5,
                        'users' => [
                            [
                                'username' => 'jmachan',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Dr.',
                                'first_name' => 'Judith Mary Ann',
                                'last_name' => 'Chan',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'jmachan@ched.gov.ph'
                            ],
                            [
                                'username' => 'lgueta',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Loupel',
                                'last_name' => 'Gueta',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'lgueta@ched.gov.ph'
                            ],
                            [
                                'username' => 'dbuenaagua',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Donald',
                                'last_name' => 'Buena Agua',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'dbuenaagua@ched.gov.ph'
                            ],
                            [
                                'username' => 'vcastelo',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Victor',
                                'last_name' => 'Castelo',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'vcastelo@ched.gov.ph'
                            ],
                            [
                                'username' => 'jkcuevas',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Jovine Krisell',
                                'last_name' => 'Cuevas',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'jkcuevas@ched.gov.ph'
                            ],
                            [
                                'username' => 'cflores',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Dr.',
                                'first_name' => 'Corazon',
                                'last_name' => 'Flores',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'cflores@ched.gov.ph'
                            ],
                            [
                                'username' => 'plabangjr',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Pelagio',
                                'last_name' => 'Labang',
                                'suffix' => 'Jr.',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'plabangjr@ched.gov.ph'
                            ],
                            [
                                'username' => 'mlontal',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Ma. Lucila',
                                'last_name' => 'Ontal',
                                'suffix' => '',
                                'position_designation' => 'Education Supervisor II',
                                'email' => 'mlontal@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Education Program Specialist II',
                        'level' => 6,
                        'users' => [
                            [
                                'username' => 'rpabiela',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Ryan Paul',
                                'last_name' => 'Abiela',
                                'suffix' => '',
                                'position_designation' => 'Education Program Specialist II',
                                'email' => 'rpabiela@ched.gov.ph'
                            ],
                            [
                                'username' => 'amendoza',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Agnes',
                                'last_name' => 'Mendoza',
                                'suffix' => '',
                                'position_designation' => 'Education Program Specialist II',
                                'email' => 'amendoza@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Technical Staff III',
                        'level' => 6,
                        'users' => [
                            [
                                'username' => 'kbdeleon',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Kathryn Beatriz',
                                'last_name' => 'De Leon',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff III',
                                'email' => 'kbdeleon@ched.gov.ph'
                            ],
                            [
                                'username' => 'kvapura',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Kim Virgil',
                                'last_name' => 'Apura',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff III',
                                'email' => 'kvapura@ched.gov.ph'
                            ],
                            [
                                'username' => 'demanalo',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Daryl Eine',
                                'last_name' => 'Manalo',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff III',
                                'email' => 'demanalo@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Technical Staff II',
                        'level' => 7,
                        'users' => [
                            [
                                'username' => 'ezara',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Ericka',
                                'last_name' => 'Zara',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'ezara@ched.gov.ph'
                            ],
                            [
                                'username' => 'msoliva',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Mariel',
                                'last_name' => 'Soliva',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'msoliva@ched.gov.ph'
                            ],
                            [
                                'username' => 'amfuentes',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Alyssa Mae',
                                'last_name' => 'Fuentes',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'amfuentes@ched.gov.ph'
                            ],
                            [
                                'username' => 'mrramirez',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Moises Rigor',
                                'last_name' => 'Ramirez',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'mrramirez@ched.gov.ph'
                            ],
                            [
                                'username' => 'abernal',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Abigail',
                                'last_name' => 'Bernal',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff II',
                                'email' => 'abernal@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Project Technical Staff I',
                        'level' => 7,
                        'users' => [
                            [
                                'username' => 'cmosende',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Christine',
                                'last_name' => 'Mosende',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff I',
                                'email' => 'cmosende@ched.gov.ph'
                            ],
                            [
                                'username' => 'ebalagbagan',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Edrich',
                                'last_name' => 'Balagbagan',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff I',
                                'email' => 'ebalagbagan@ched.gov.ph'
                            ],
                            [
                                'username' => 'jcajefe',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Ms.',
                                'first_name' => 'Jhanina',
                                'last_name' => 'Cajefe',
                                'suffix' => '',
                                'position_designation' => 'Project Technical Staff I',
                                'email' => 'jcajefe@ched.gov.ph'
                            ]
                        ]
                    ],
                    [
                        'description' => 'Job Order',
                        'level' => 7,
                        'users' => [
                            [
                                'username' => 'jjbasco',
                                'password' => Hash::make('chedro1234'),
                                'prefix' => 'Mr.',
                                'first_name' => 'Jonathan Jaylord',
                                'last_name' => 'Basco',
                                'suffix' => '',
                                'position_designation' => 'Job Order',
                                'email' => 'jjbasco@ched.gov.ph'
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
                        'position_designation' => $user['position_designation'],
                        'email' => $user['email']
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
