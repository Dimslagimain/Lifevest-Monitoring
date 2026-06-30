<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aircraft = [
            // ===== GARUDA INDONESIA (airline_id: 1) =====
            // B737-800 Layout: b737-e46
            ['registration' => 'PK-GFD', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFG', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFI', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFM', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFP', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMA', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMF', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMM', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFF', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFH', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFR', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFU', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFW', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFX', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNA', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNC', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNE', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNF', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNM', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNN', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNQ', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNR', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMP', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GMU', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMV', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMW', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMX', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMY', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFJ', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFQ', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNG', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GNH', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GUG', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFS', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMD', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GMC', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GMI', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GFV', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GUF', 'type' => 'B737-800', 'layout' => 'b737-e46', 'status' => 'prolong', 'airline_id' => 1],

            // B737 MAX 8 Layout: b737-e46
            ['registration' => 'PK-GDC', 'type' => 'B737 MAX 8', 'layout' => 'b737-e46', 'status' => 'active', 'airline_id' => 1],

            // B737-800 Layout: b737-e48
            ['registration' => 'PK-GUH', 'type' => 'B737-800', 'layout' => 'b737-e48', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GUI', 'type' => 'B737-800', 'layout' => 'b737-e48', 'status' => 'active', 'airline_id' => 1],

            // B737-800 Layout: b737-e47
            ['registration' => 'PK-GUD', 'type' => 'B737-800', 'layout' => 'b737-e47', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GUE', 'type' => 'B737-800', 'layout' => 'b737-e47', 'status' => 'active', 'airline_id' => 1],

            // B737-800 Layout: b737-e49
            ['registration' => 'PK-GUA', 'type' => 'B737-800', 'layout' => 'b737-e49', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GUC', 'type' => 'B737-800', 'layout' => 'b737-e49', 'status' => 'active', 'airline_id' => 1],

            // B777-300 Layout: b777-2class
            ['registration' => 'PK-GIA', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GIC', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GIH', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GII', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GIJ', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GIK', 'type' => 'B777-300', 'layout' => 'b777-2class', 'status' => 'active', 'airline_id' => 1],

            // B777-300 Layout: b777-3class
            ['registration' => 'PK-GIF', 'type' => 'B777-300', 'layout' => 'b777-3class', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GIG', 'type' => 'B777-300', 'layout' => 'b777-3class', 'status' => 'active', 'airline_id' => 1],

            // A330-900 Layout: a330-900a
            ['registration' => 'PK-GHE', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GHF', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GHG', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'prolong', 'airline_id' => 1],

            // A330-900 Layout: a330-900b
            ['registration' => 'PK-GHH', 'type' => 'A330-900', 'layout' => 'a330-900b', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GHI', 'type' => 'A330-900', 'layout' => 'a330-900b', 'status' => 'prolong', 'airline_id' => 1],

            // A330-300 Layout: a330-300a
            ['registration' => 'PK-GPZ', 'type' => 'A330-300', 'layout' => 'a330-300a', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GHA', 'type' => 'A330-300', 'layout' => 'a330-300a', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GHC', 'type' => 'A330-300', 'layout' => 'a330-300a', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GHD', 'type' => 'A330-300', 'layout' => 'a330-300a', 'status' => 'active', 'airline_id' => 1],

            // A330-300 Layout: a330-300b
            ['registration' => 'PK-GPU', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GPV', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GPW', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GPY', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GPR', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPT', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPX', 'type' => 'A330-300', 'layout' => 'a330-300b', 'status' => 'prolong', 'airline_id' => 1],

            // A330-300 Layout: a330-300c
            ['registration' => 'PK-GPC', 'type' => 'A330-300', 'layout' => 'a330-300c', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPG', 'type' => 'A330-300', 'layout' => 'a330-300c', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPE', 'type' => 'A330-341', 'layout' => 'a330-300c', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPF', 'type' => 'A330-341', 'layout' => 'a330-300c', 'status' => 'prolong', 'airline_id' => 1],

            // A330-300 Layout: a330-300cargo
            ['registration' => 'PK-GPA', 'type' => 'A330-300', 'layout' => 'a330-300cargo', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPD', 'type' => 'A330-300', 'layout' => 'a330-300cargo', 'status' => 'prolong', 'airline_id' => 1],

            // A330-200 Layout: a330-200a
            ['registration' => 'PK-GPO', 'type' => 'A330-200', 'layout' => 'a330-200a', 'status' => 'active', 'airline_id' => 1],
            ['registration' => 'PK-GPM', 'type' => 'A330-200', 'layout' => 'a330-200a', 'status' => 'prolong', 'airline_id' => 1],

            // A330-200 Layout: a330-200b
            ['registration' => 'PK-GPQ', 'type' => 'A330-200', 'layout' => 'a330-200b', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPS', 'type' => 'A330-200', 'layout' => 'a330-200b', 'status' => 'prolong', 'airline_id' => 1],
            ['registration' => 'PK-GPL', 'type' => 'A330-200', 'layout' => 'a330-200b', 'status' => 'prolong', 'airline_id' => 1],

            // ===== CITILINK (airline_id: 2) =====
            // ATR72-600 Layout: atr72
            ['registration' => 'PK-GJT', 'type' => 'ATR72-600', 'layout' => 'atr72', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GJV', 'type' => 'ATR72-600', 'layout' => 'atr72', 'status' => 'active', 'airline_id' => 2],

            // ===== CITILINK (airline_id: 2) =====
            ['registration' => 'PK-GLE', 'type' => 'A320-233', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLK', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQA', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQU', 'type' => 'A320-214', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTC', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GGC', 'type' => 'A320-233', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLG', 'type' => 'A320-233', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLI', 'type' => 'A320-233', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLL', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLM', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLN', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLO', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLP', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLQ', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLR', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLS', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLT', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLU', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLV', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLW', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLX', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLY', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GLZ', 'type' => 'A320-241', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQC', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQD', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQE', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQF', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQG', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQH', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQI', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQJ', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQK', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQL', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQM', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQN', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQO', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQP', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQQ', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQR', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQS', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GQT', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTA', 'type' => 'A320-200', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTD', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTE', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTF', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTG', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTH', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTJ', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTI', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
            ['registration' => 'PK-GTK', 'type' => 'A320-251N', 'layout' => 'a320a', 'status' => 'active', 'airline_id' => 2],
        ];

        $i = 1;
        foreach ($aircraft as $data) {
            // Assign Authentic Part Numbers based on index to hit required volumes
            if ($i <= 60) {
                $data['pn_adult'] = 'P0723-103W';
                $data['pn_crew'] = ($i % 2 == 0) ? 'P0723-103WC' : 'P01074-201WC';
            } elseif ($i <= 110) {
                $data['pn_adult'] = 'P01074-201W';
                $data['pn_crew'] = ($i % 5 == 0) ? '63600-505:70167' : 'P0723-103WC';
            } else {
                $data['pn_adult'] = 'P01074-221W';
                $data['pn_crew'] = ($i % 10 == 0) ? 'P01074-205WC' : 'P01074-201WC';
            }

            $data['pn_infant'] = 'P0640-101';

            // Set Icon
            if (str_contains($data['type'], '737') || str_contains($data['type'], '320')) {
                $data['icon'] = '✈️';
            } elseif (str_contains($data['type'], '777') || str_contains($data['type'], '330')) {
                $data['icon'] = '🛫';
            } else {
                $data['icon'] = '🛩️';
            }

            Aircraft::updateOrCreate(
                ['registration' => $data['registration']],
                $data
            );
            $i++;
        }
    }
}
