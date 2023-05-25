<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $document_types = [
            [
                'code'=> '00',
                'description' => 'Application/COPC',
                'days' => '40'
            ],
            [
                'code'=> '01',
                'description' => 'Curriculum',
                'days' => '3'
            ],
            [
                'code'=> '02',
                'description' => 'Additional Major',
                'days' => '3'
            ],
            [
                'code'=> '03',
                'description' => 'Change of Nomenclature',
                'days' => '3'
            ],
            [
                'code'=> '04',
                'description' => 'NSTP Enrollment/Request',
                'days' => '3'
            ],
            [
                'code'=> '05',
                'description' => 'Request for Data/Certification',
                'days' => '3'
            ],
            [
                'code'=> '06',
                'description' => 'SEC Endorsement',
                'days' => '3'
            ],
            [
                'code'=> '07',
                'description' => 'COR for LUCs',
                'days' => '3'
            ],
            [
                'code'=> '08',
                'description' => 'TOSF Increase',
                'days' => '20'
            ],
            [
                'code'=> '09',
                'description' => 'Communication from/to CHED Central Office',
                'days' => '3'
            ],
            [
                'code'=> '10',
                'description' => 'Complaint',
                'days' => '20'
            ],
            [
                'code'=> '11',
                'description' => 'Communication from/to Other Agencies ',
                'days' => '3'
            ],
            [
                'code'=> '12',
                'description' => 'CHED Endorsement',
                'days' => '3'
            ],
            [
                'code'=> '13',
                'description' => 'Contract of Affiliation/MOA',
                'days' => '3'
            ],
            [
                'code'=> '14',
                'description' => 'Foreign Student Data',
                'days' => '3'
            ],
            [
                'code'=> '15',
                'description' => 'Email',
                'days' => '3'
            ],
            [
                'code'=> '16',
                'description' => 'Academic Calendar',
                'days' => '3'
            ],
            [
                'code'=> '17',
                'description' => 'Other Communications',
                'days' => '3'
            ],
            [
                'code'=> '18',
                'description' => 'CAV',
                'days' => '7'
            ],
            [
                'code'=> '19',
                'description' => 'Special Order',
                'days' => '20'
            ],
            [
                'code'=> '20',
                'description' => 'Scholarship concerns',
                'days' => '3'
            ],
            [
                'code'=> '21',
                'description' => 'Enrollemnt List',
                'days' => '3'
            ],
            [
                'code'=> '22',
                'description' => 'Office Memorandum',
                'days' => '3'
            ],
            [
                'code'=> '23',
                'description' => 'Contracts (COS and JO)',
                'days' => '3'
            ],
            [
                'code'=> '24',
                'description' => 'Other Administrative matters',
                'days' => '3'
            ],
        ];

        $document_types = DocumentType::insert($document_types);
    }
}
