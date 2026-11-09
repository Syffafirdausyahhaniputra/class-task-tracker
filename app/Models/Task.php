<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'due_date', 'class_id'];

    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_completions', 'task_id', 'student_id')
            ->withPivot('completed_at');
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'class_id' => 'integer',
            'created_by' => 'integer',
        ];
    }
}
