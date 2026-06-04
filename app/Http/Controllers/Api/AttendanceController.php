<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AwsRekognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    protected function guardStudent(Request $request)
    {
        $header = $request->bearerToken();
        if (!$header) return null;
        $hash = hash('sha256', $header);
        return Student::where('api_token', $hash)->first();
    }

    public function qrCheckIn(Request $request)
    {
        // QR check-in removed; endpoint deprecated.
        return response()->json(['message' => 'QR check-in is disabled'], 410);
    }

    public function imageCheckIn(Request $request, AwsRekognitionService $rek)
    {
        $student = $this->guardStudent($request);
        if (!$student) return response()->json(['message' => 'Unauthenticated'], 401);

        $request->validate(['image' => 'required|image']);
        $path = $request->file('image')->store('attendance_images', 'public');
        $url = Storage::url($path);

        $result = $rek->compareWithStudentImage($student, storage_path('app/public/'.$path));

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'method' => 'image',
            'image_url' => $url,
            'confidence' => $result['confidence'] ?? null,
            'meta' => $result,
        ]);

        return response()->json(['message' => 'Image processed', 'attendance' => $attendance, 'rekognition' => $result]);
    }
}
