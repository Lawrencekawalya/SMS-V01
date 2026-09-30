<?php

namespace Database\Seeders;

use App\Models\AwardClassification;
use App\Models\GradingScaleTier;
use Illuminate\Database\Seeder;

class GradingPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();
    }
}
