<?php

namespace App\Models;

use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name'];

    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory;

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'class_id');
    }
}
