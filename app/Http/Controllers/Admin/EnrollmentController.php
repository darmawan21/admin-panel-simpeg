<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EnrollmentController extends Controller
{
    public function create(Employee $employee)
    {
        return view('admin.employees.enroll', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'required|image|max:5120', // Max 5MB per image
        ]);

        $embeddings = [];
        $pythonServiceUrl = config('services.face_extraction.url', 'http://localhost:8001') . '/extract-embedding';

        foreach ($request->file('photos') as $index => $photo) {
            try {
                $response = Http::attach(
                    'file',
                    file_get_contents($photo->getRealPath()),
                    $photo->getClientOriginalName()
                )->post($pythonServiceUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['embedding'])) {
                        $embeddings[] = $data['embedding'];
                    }
                } else {
                    return back()->with('error', "Failed to extract face from Photo " . ($index + 1) . ". Please ensure the photo is clear and contains exactly one face.");
                }
            } catch (\Exception $e) {
                return back()->with('error', "Could not connect to the Face Extraction Microservice. Is it running?");
            }
        }

        if (count($embeddings) === 0) {
            return back()->with('error', 'No valid faces were detected in the uploaded photos.');
        }

        // Average the embeddings to get a single enrollment embedding
        $averagedEmbedding = $this->averageEmbeddings($embeddings);

        // Save to Database
        FaceEmbedding::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'embedding' => $averagedEmbedding,
                'enrollment_status' => 'enrolled',
                'enrolled_at' => now(),
            ]
        );

        $employee->update(['enrollment_status' => 'enrolled']);

        return redirect()->route('admin.employees.index')->with('success', 'Face enrollment successful. Generated an averaged embedding from ' . count($embeddings) . ' photos.');
    }

    private function averageEmbeddings(array $embeddings): array
    {
        $count = count($embeddings);
        $dimension = count($embeddings[0]);
        $averaged = array_fill(0, $dimension, 0.0);

        foreach ($embeddings as $embedding) {
            for ($i = 0; $i < $dimension; $i++) {
                $averaged[$i] += $embedding[$i];
            }
        }

        for ($i = 0; $i < $dimension; $i++) {
            $averaged[$i] /= $count;
        }

        return $averaged;
    }
}
