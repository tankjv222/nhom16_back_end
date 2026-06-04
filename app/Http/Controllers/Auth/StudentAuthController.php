<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentAuthController extends Controller
{
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
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $password = $request->password;

        if (!$student->verifyPassword($password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = Str::random(60);
        $student->forceFill(['api_token' => hash('sha256', $token)])->save();

        return response()->json(['token' => $token, 'student' => $student]);
    }
}
