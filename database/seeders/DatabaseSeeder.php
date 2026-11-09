<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $classA = SchoolClass::create(['name' => 'X-A']);
        $classB = SchoolClass::create(['name' => 'X-B']);

        $teacher = User::factory()->teacher()->create([
            'name' => 'Guru Contoh',
            'email' => 'guru@sekolah.test',
        ]);

        $student1 = User::factory()->studentIn($classA)->create([
            'name' => 'Siswa 1',
            'email' => 'siswa1@sekolah.test',
        ]);
        $student2 = User::factory()->studentIn($classA)->create([
            'name' => 'Siswa 2',
            'email' => 'siswa2@sekolah.test',
        ]);
        $student3 = User::factory()->studentIn($classB)->create([
            'name' => 'Siswa 3',
            'email' => 'siswa3@sekolah.test',
        ]);

        $task1 = Task::factory()->create([
            'title' => 'Mengerjakan latihan matematika',
            'class_id' => $classA->id,
            'created_by' => $teacher->id,
        ]);
        $task2 = Task::factory()->create([
            'title' => 'Membaca materi IPA',
            'class_id' => $classA->id,
            'created_by' => $teacher->id,
        ]);
        $task3 = Task::factory()->create([
            'title' => 'Membuat ringkasan sejarah',
            'class_id' => $classB->id,
            'created_by' => $teacher->id,
        ]);

        // Penyelesaian disimpan per siswa, bukan di tabel tugas.
        $task1->completions()->create([
            'student_id' => $student1->id,
            'completed_at' => now(),
        ]);
        $task3->completions()->create([
            'student_id' => $student3->id,
            'completed_at' => now(),
        ]);
    }
}
