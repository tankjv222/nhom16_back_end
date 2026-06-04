<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Services\AwsRekognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentWebController extends Controller
{
    public function showLogin()
    {
        return view('student.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'student_id' => 'required_without:email',
            'password' => 'required',
        ]);

        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::where('student_id', $request->student_id)->first();
        } elseif ($request->filled('email')) {
            $student = Student::where('email', $request->email)->first();
        }

        if (!$student) {
            return back()->withErrors(['student_id' => 'Không tìm thấy sinh viên'])->withInput();
        }

        $password = $request->password;

        if (!$student->verifyPassword($password)) {
            return back()->withErrors(['password' => 'Mật khẩu không đúng'])->withInput();
        }

        session(['student_id' => $student->id]);
        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('student_id');
        return redirect()->route('student.login');
    }

    protected function currentStudent(Request $request)
    {
        $id = $request->session()->get('student_id');
        return $id ? Student::find($id) : null;
    }

    public function dashboard(Request $request)
    {
        $student = $this->currentStudent($request);
        if (!$student) return redirect()->route('student.login');

        $attendances = Attendance::where('student_id', $student->id)->latest()->limit(20)->get();
        return view('student.dashboard', compact('student','attendances'));
    }

    public function imageCheckIn(Request $request, AwsRekognitionService $rek)
    {
        $student = $this->currentStudent($request);
        if (!$student) return redirect()->route('student.login');

        $request->validate(['image' => 'required|image']);
        $path = $request->file('image')->store('attendance_images', 'public');
        $url = Storage::url($path);

        $result = $rek->compareWithStudentImage($student, storage_path('app/public/'.$path));

        Attendance::create([
            'student_id' => $student->id,
            'method' => 'image',
            'image_url' => $url,
            'confidence' => $result['confidence'] ?? null,
            'meta' => $result,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Image processed', 'confidence' => $result['confidence'] ?? null, 'rekognition' => $result]);
        }

        return back()->with('status', 'Ảnh đã được gửi, kết quả: '.($result['confidence'] ?? 'N/A'));
    }
}
