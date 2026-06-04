<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['student_id','event_id','method','image_url','confidence','meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
