<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\FaceEmbedding;
use App\Models\WorkSite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo Employees ─────────────────────────────────────────────

        $admin = Employee::create([
            'employee_id' => 'ADM001',
            'name' => 'Admin User',
            'email' => 'admin@simpeg.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'enrollment_status' => 'enrolled',
            'is_active' => true,
        ]);

        $supervisor = Employee::create([
            'employee_id' => 'SUP001',
            'name' => 'Supervisor User',
            'email' => 'supervisor@simpeg.test',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'enrollment_status' => 'enrolled',
            'is_active' => true,
        ]);

        $employee1 = Employee::create([
            'employee_id' => 'EMP001',
            'name' => 'Ahmad Fadli',
            'email' => 'ahmad@simpeg.test',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'enrollment_status' => 'enrolled',
            'is_active' => true,
        ]);

        $employee2 = Employee::create([
            'employee_id' => 'EMP002',
            'name' => 'Siti Rahayu',
            'email' => 'siti@simpeg.test',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'enrollment_status' => 'pending',
            'is_active' => true,
        ]);

        $employee3 = Employee::create([
            'employee_id' => 'EMP003',
            'name' => 'Budi Santoso',
            'email' => 'budi@simpeg.test',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'enrollment_status' => 'enrolled',
            'is_active' => true,
        ]);

        // ── Pre-enrolled Face Embeddings (mock 128-dim vectors) ────────

        $mockEmbedding = $this->generateMockEmbedding();

        foreach ([$admin, $supervisor, $employee1, $employee3] as $emp) {
            FaceEmbedding::create([
                'employee_id' => $emp->id,
                'embedding' => $mockEmbedding,
                'enrollment_status' => 'enrolled',
                'enrolled_at' => now(),
            ]);
        }

        // ── Demo Work Sites ────────────────────────────────────────────

        WorkSite::create([
            'name' => 'Kantor Pusat',
            'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
            'latitude' => -6.2088000,
            'longitude' => 106.8456000,
            'geofence_radius' => 150.00,
            'geofence_policy' => 'warn',
            'is_active' => true,
        ]);

        WorkSite::create([
            'name' => 'Kantor Cabang Bandung',
            'address' => 'Jl. Asia Afrika No. 25, Bandung',
            'latitude' => -6.9175000,
            'longitude' => 107.6191000,
            'geofence_radius' => 100.00,
            'geofence_policy' => 'reject',
            'is_active' => true,
        ]);
    }

    /**
     * Generate a normalized 128-dimensional mock embedding.
     */
    private function generateMockEmbedding(): array
    {
        $embedding = [];
        $sum = 0.0;

        for ($i = 0; $i < 128; $i++) {
            $val = (mt_rand(-1000, 1000) / 1000.0);
            $embedding[] = $val;
            $sum += $val * $val;
        }

        // L2 normalize
        $norm = sqrt($sum);
        if ($norm > 0) {
            for ($i = 0; $i < 128; $i++) {
                $embedding[$i] = round($embedding[$i] / $norm, 6);
            }
        }

        return $embedding;
    }
}
