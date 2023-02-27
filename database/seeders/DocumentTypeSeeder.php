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
                'code'=> '23-00',
                'description' => 'Application/COPC',
                'days' => '40'
            ],
            [
                'code'=> '23-01',
                'description' => 'Curriculum',
                'days' => '3'
            ],
            [
                'code'=> '23-02',
                'description' => 'Additional major',
                'days' => '3'
            ],
            [
                'code'=> '23-03',
                'description' => 'Change of nomenclature',
                'days' => '3'
            ],
            [
                'code'=> '23-04',
                'description' => 'NSTP Enrollment/Request',
                'days' => '3'
            ],
            [
                'code'=> '23-05',
                'description' => 'Request for Data/Certification',
                'days' => '3'
            ],
            [
                'code'=> '23-06',
                'description' => 'SEC Endorsement',
                'days' => '3'
            ],
            [
                'code'=> '23-07',
                'description' => 'COR for LUCs',
                'days' => '3'
            ],
            [
                'code'=> '23-08',
                'description' => 'TOSF Increase',
                'days' => '20'
            ],
            [
                'code'=> '23-09',
                'description' => 'Communication from/to CHED Central Office',
                'days' => '3'
            ],
            [
                'code'=> '23-10',
                'description' => 'Complaint',
                'days' => '20'
            ],
            [
                'code'=> '23-11',
                'description' => 'Communication from/to other agencies ',
                'days' => '3'
            ],
            [
                'code'=> '23-12',
                'description' => 'CHED Endorsement',
                'days' => '3'
            ],
            [
                'code'=> '23-13',
                'description' => 'Contract of Affiliation/MOA',
                'days' => '3'
            ],
            [
                'code'=> '23-14',
                'description' => 'Foreign student data',
                'days' => '3'
            ],
            [
                'code'=> '23-15',
                'description' => 'Email',
                'days' => '3'
            ],
            [
                'code'=> '23-16',
                'description' => 'Academic Calendar',
                'days' => '3'
            ],
            [
                'code'=> '23-17',
                'description' => 'Other Communications',
                'days' => '3'
            ],
            [
                'code'=> '23-18',
                'description' => 'CAV',
                'days' => '7'
            ],
            [
                'code'=> '23-19',
                'description' => 'Special Order',
                'days' => '20'
            ],
            [
                'code'=> '23-20',
                'description' => 'Scholarship concerns',
                'days' => '3'
            ],
            [
                'code'=> '23-21',
                'description' => 'Enrollemnt List',
                'days' => '3'
            ],
            [
                'code'=> '23-22',
                'description' => 'Office Memorandum',
                'days' => '3'
            ],
            [
                'code'=> '23-23',
                'description' => 'Contracts (COS and JO)',
                'days' => '3'
            ],
            [
                'code'=> '23-24',
                'description' => 'Other Administrative matters',
                'days' => '3'
            ],
        ];

        $document_types = DocumentType::insert($document_types);
    }
}
