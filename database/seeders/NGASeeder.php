<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Nga;



class NGASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nga = [
            [
                'code'=> 'DAR',
                'description' => 'Department of Agrarian Reform',
                'email' => 'contact_us@dar.gov.ph'
            ],
            [
                'code'=> 'DA',
                'description' => 'Department of Agriculture',
                'email' => 'osec@da.gov.ph'
            ],
            [
                'code'=> 'DBM',
                'description' => 'Department of Budget and Management',
                'email' => 'csodesk@dbm.gov.ph'
            ],
            [
                'code'=> 'DepEd',
                'description' => 'Department of Education',
                'email' => 'osec@deped.gov.ph'
            ],
            [
                'code'=> 'DOE',
                'description' => 'Department of Energy',
                'email' => 'infocenter@doe.gov.ph'
            ],
            [
                'code'=> 'DENR',
                'description' => 'Department of Environment and Natural Resources',
                'email' => 'aksyonkalikasan@denr.gov.ph'
            ],
            [
                'code'=> 'DOF',
                'description' => 'Department of Finance',
                'email' => 'secfin@dof.gov.ph'
            ],
            [
                'code'=> 'DFA',
                'description' => 'Department of Foreign Affairs',
                'email' => 'oca.concerns@dfa.gov.ph'
            ],
            [
                'code'=> 'DOH',
                'description' => 'Department of Health',
                'email' => 'dohosec@doh.gov.ph'
            ],
            [
                'code'=> 'DHSUD',
                'description' => 'Department of Human Settlements and Urban Development',
                'email' => 'osec@dhsud.gov.ph'
            ],
            [
                'code'=> 'DICT',
                'description' => 'Department of Information and Communications Technology',
                'email' => 'information@dict.gov.ph'
            ],
            [
                'code'=> 'DILG',
                'description' => 'Department of the Interior and Local Government',
                'email' => 'bcabalosjr@dilg.gov.ph'
            ],
            [
                'code'=> 'DOJ',
                'description' => 'Department of Justice',
                'email' => 'dojac@doj.gov.ph'
            ],
            [
                'code'=> 'DOLE',
                'description' => 'Department of Labor and Employment',
                'email' => 'ncr@dole.gov.ph'
            ],
            [
                'code'=> 'DMW',
                'description' => 'Department of Migrant Workers',
                'email' => 'connect@dmw.gov.ph'
            ],
            [
                'code'=> 'DND',
                'description' => 'Department of National Defense',
                'email' => 'comms@dnd.gov.ph'
            ],
            [
                'code'=> 'DPWH',
                'description' => 'Department of Public Works and Highways',
                'email' => 'citizens_feedback@dpwh.gov.ph'
            ],
            [
                'code'=> 'DOST',
                'description' => 'Department of Science and Technology',
                'email' => 'info@asti.dost.gov.ph'
            ],
            [
                'code'=> 'DSWD',
                'description' => 'Department of Social Welfare and Development',
                'email' => 'ictms-dpo@dswd.gov.ph'
            ],
            [
                'code'=> 'DOT',
                'description' => 'Department of Tourism',
                'email' => 'dot4a@tourism.gov.ph'
            ],
            [
                'code'=> 'DTI',
                'description' => 'Department of Trade and Industry',
                'email' => 'consumercare@dti.gov.ph'
            ],
            [
                'code'=> 'DOTr',
                'description' => 'Department of Transportation',
                'email' => 'icd@dotc.gov.ph'
            ],


        ];

        $nga = Nga::insert($nga);
    }
}   
