<?php

namespace Database\Seeders;

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $criterias = [
                [
                    'code' => 'C1',
                    'name' => 'Tingkat Kerusakan Fisik',
                    'type' => CriteriaType::Benefit,
                    'weight' => 0.30,
                    'input_type' => CriteriaInputType::Categorical,
                    'is_system' => true,
                    'sub_criterias' => [
                        [
                            'description' => 'Rusak Ringan (Komponen minor)',
                            'crisp_value' => 1,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                        [
                            'description' => 'Rusak Sedang (Fungsi terganggu sebagian)',
                            'crisp_value' => 3,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                        [
                            'description' => 'Rusak Berat (Mati total / tidak dapat digunakan)',
                            'crisp_value' => 5,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                    ],
                ],
                [
                    'code' => 'C2',
                    'name' => 'Perkiraan Biaya Servis',
                    'type' => CriteriaType::Cost,
                    'weight' => 0.25,
                    'input_type' => CriteriaInputType::Range,
                    'is_system' => true,
                    'sub_criterias' => [
                        [
                            'description' => '< Rp 250.000 (Sangat Terjangkau)',
                            'crisp_value' => 1,
                            'min_value' => 0.00,
                            'max_value' => 250000.00,
                        ],
                        [
                            'description' => 'Rp 250.000 - Rp 750.000 (Biaya Menengah)',
                            'crisp_value' => 3,
                            'min_value' => 250001.00,
                            'max_value' => 750000.00,
                        ],
                        [
                            'description' => '> Rp 750.000 (Biaya Tinggi / Penggantian Besar)',
                            'crisp_value' => 5,
                            'min_value' => 750001.00,
                            'max_value' => 999999999.00,
                        ],
                    ],
                ],
                [
                    'code' => 'C3',
                    'name' => 'Urgensi Lokasi Ruangan',
                    'type' => CriteriaType::Benefit,
                    'weight' => 0.25,
                    'input_type' => CriteriaInputType::Categorical,
                    'is_system' => true,
                    'sub_criterias' => [
                        [
                            'description' => 'Ruang Penunjang / Gudang / Selasar (Prioritas Rendah)',
                            'crisp_value' => 1,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                        [
                            'description' => 'Ruang Guru / Kantor TU / UKS (Prioritas Sedang)',
                            'crisp_value' => 3,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                        [
                            'description' => 'Ruang Kelas / Lab Komputer / Lab Praktik (Prioritas Tinggi)',
                            'crisp_value' => 5,
                            'min_value' => null,
                            'max_value' => null,
                        ],
                    ],
                ],
                [
                    'code' => 'C4',
                    'name' => 'Usia Aset',
                    'type' => CriteriaType::Benefit,
                    'weight' => 0.20,
                    'input_type' => CriteriaInputType::Range,
                    'is_system' => true,
                    'sub_criterias' => [
                        [
                            'description' => '< 2 Tahun (Aset Relatif Baru)',
                            'crisp_value' => 1,
                            'min_value' => 0.00,
                            'max_value' => 1.99,
                        ],
                        [
                            'description' => '2 - 4 Tahun (Usia Menengah)',
                            'crisp_value' => 3,
                            'min_value' => 2.00,
                            'max_value' => 4.99,
                        ],
                        [
                            'description' => '>= 5 Tahun (Aset Lama / Rawan Aus)',
                            'crisp_value' => 5,
                            'min_value' => 5.00,
                            'max_value' => 999.00,
                        ],
                    ],
                ],
            ];

            foreach ($criterias as $criteriaData) {
                $subCriterias = $criteriaData['sub_criterias'];
                unset($criteriaData['sub_criterias']);

                $criteria = Criteria::updateOrCreate(
                    ['code' => $criteriaData['code']],
                    $criteriaData
                );

                foreach ($subCriterias as $sub) {
                    SubCriteria::updateOrCreate(
                        [
                            'criteria_id' => $criteria->id,
                            'description' => $sub['description'],
                        ],
                        [
                            'crisp_value' => $sub['crisp_value'],
                            'min_value' => $sub['min_value'],
                            'max_value' => $sub['max_value'],
                        ]
                    );
                }
            }
        });
    }
}
