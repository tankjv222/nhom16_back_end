<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'email',
        'first_name',
        'last_name',
        'password',
        'phone',
        'class_id',
        'faculty',
        'birthdate',
        'photo_url',
        'qrcode_token',
        'status',
        'registered_at',
        'note',
        'academic_year',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'registered_at' => 'datetime',
    ];

    public function verifyPassword(string $plainPassword): bool
    {
        if (!$this->password) {
            return false;
        }

        if (str_starts_with($this->password, '$2y$') || str_starts_with($this->password, '$2a$') || str_starts_with($this->password, '$2b$') || str_starts_with($this->password, '$argon2i$') || str_starts_with($this->password, '$argon2id$')) {
            return Hash::check($plainPassword, $this->password);
        }

        return md5($plainPassword) === $this->password;
    }
}
