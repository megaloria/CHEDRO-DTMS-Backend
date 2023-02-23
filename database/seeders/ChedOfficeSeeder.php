<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChedOffice;


class ChedOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $ched = [
            [
                'code'=> 'OCC',
                'description' => 'Office Of The Chairperson and Commissioners',
                'email' => 'chairperson@ched.gov.ph'
            ],
            [
                'code'=> 'OED',
                'description' => 'Office of the Executive Director',
                'email' => 'executivedirector@ched.gov.ph'
            ],
            [
                'code'=> 'OPSD',
                'description' => 'Office of Programs and Standards Development',
                'email' => 'opsd2019@ched.gov.ph'
            ],
            [
                'code'=> 'OSDS',
                'description' => 'Office of Student Development and Services',
                'email' => 'osds@ched.gov.ph'
            ],
            [
                'code'=> 'OPRKM',
                'description' => 'Office of Planning, Research & Knowledge Management',
                'email' => 'oprkmdirector@ched.gov.ph'
            ],
            [
                'code'=> 'OIQAG',
                'description' => 'Office of Institutional Quality Assurance & Governance',
                'email' => 'info@ched.gov.ph'
            ],
            [
                'code'=> 'LLS',
                'description' => 'Legal and Legislative Service',
                'email' => 'chedlegal@ched.gov.ph'
            ],
            [
                'code'=> 'AFMS',
                'description' => 'Administrative, Financial & Management Service',
                'email' => 'afms.od@ched.gov.ph'
            ],
            [
                'code'=> 'IAS',
                'description' => 'International Affairs Staff',
                'email' => 'dohosec@doh.gov.ph'
            ],
            [
                'code'=> 'HEDFS',
                'description' => 'Higher Education Development Fund Staff',
                'email' => '@ched.gov.ph'
            ],
            [
                'code'=> 'LGSO',
                'description' => 'Local Graduate Scholarship Office',
                'email' => 'info@ched.gov.ph'
            ],
            [
                'code'=> 'UniFAST',
                'description' => 'Unified Student Financial Assistance System for Tertiary Education',
                'email' => 'info@ched.gov.ph'
            ],
            [
                'code'=> 'PCARI',
                'description' => 'Philippine California Advanced Research Institutes',
                'email' => 'info@ched.gov.ph'
            ],
            [
                'code'=> 'HRDD',
                'description' => 'Human Resource Development Division',
                'email' => 'info@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO I',
                'description' => 'Commission on Higher Education Regional Office I',
                'email' => 'chedro1@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO II',
                'description' => 'Commission on Higher Education Regional Office II',
                'email' => 'chedro2@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO III',
                'description' => 'Commission on Higher Education Regional Office III',
                'email' => 'chedro3@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO MIMAROPA',
                'description' => 'Commission on Higher Education Regional Office MIMAROPA',
                'email' => 'chedro4b@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO V',
                'description' => 'Commission on Higher Education Regional Office V',
                'email' => 'chedro5@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO VI',
                'description' => 'Commission on Higher Education Regional Office VI',
                'email' => 'chedro6@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO VII',
                'description' => 'Commission on Higher Education Regional Office VII',
                'email' => 'chedro7@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO VIII',
                'description' => 'Commission on Higher Education Regional Office VIII',
                'email' => 'chedro8@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO IX',
                'description' => 'Commission on Higher Education Regional Office IX',
                'email' => 'chedro9@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO X',
                'description' => 'Commission on Higher Education Regional Office X',
                'email' => 'chedro10@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO XI',
                'description' => 'Commission on Higher Education Regional Office XI',
                'email' => 'chedro11@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO XII',
                'description' => 'Commission on Higher Education Regional Office XII',
                'email' => 'chedro12@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO NCR',
                'description' => 'Commission on Higher Education Regional Office NCR',
                'email' => 'chedncr@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO CAR',
                'description' => 'Commission on Higher Education Regional Office CAR',
                'email' => 'chedcar@ched.gov.ph'
            ],
            [
                'code'=> 'CHEDRO CARAGA',
                'description' => 'Commission on Higher Education Regional Office CARAGA',
                'email' => 'chedcaraga@ched.gov.ph'
            ],

        ];

        $ched = ChedOffice::insert($ched);
    }
}

