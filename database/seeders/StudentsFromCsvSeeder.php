<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StudentsFromCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('savsoft_users.csv');
        if (!file_exists($path)) {
            $path = base_path('../savsoft_users.csv');
        }
        if (!file_exists($path)) {
            $this->command->info('CSV not found: '.$path);
            return;
        }

        $handle = fopen($path, 'r');
        $header = null;
        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $data = array_combine($header, $row);
            if (empty($data['studentid']) && empty($data['email'])) {
                continue;
            }

            Student::updateOrCreate(
                ['student_id' => $data['studentid'] ?: null],
                [
                    'email' => ($data['email'] ?? '') !== 'NULL' ? $data['email'] : null,
                    'first_name' => $data['first_name'] ?: null,
                    'last_name' => $data['last_name'] ?: null,
                    'password' => $data['password'] ?: null,
                    'phone' => $data['contact_no'] ?: null,
                    'class_id' => $data['classid'] ?: null,
                    'faculty' => $data['facultyid'] ?: null,
                    'birthdate' => ($data['birthdate'] && $data['birthdate'] !== 'NULL') ? $data['birthdate'] : null,
                    'photo_url' => ($data['photo'] ?? '') !== 'NULL' ? $data['photo'] : null,
                    'status' => $data['user_status'] ?: 'Active',
                    'registered_at' => ($data['registered_date'] ?? '') !== 'NULL' ? $data['registered_date'] : null,
                    'note' => $data['note'] ?: null,
                    'academic_year' => $data['academic_year'] ?: null,
                    'qrcode_token' => $data['qrcode'] ?: null,
                ]
            );
        }
        fclose($handle);
        $this->command->info('Imported students from CSV');
    }
}
