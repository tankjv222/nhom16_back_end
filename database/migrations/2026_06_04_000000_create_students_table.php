<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->nullable()->index();
            $table->string('email')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('class_id')->nullable();
            $table->string('faculty')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('qrcode_token')->nullable()->index();
            $table->string('status')->default('Active');
            $table->timestamp('registered_at')->nullable();
            $table->text('note')->nullable();
            $table->string('academic_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
