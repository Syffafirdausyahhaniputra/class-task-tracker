<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'class_id' => $this->class_id,
            'created_by' => $this->created_by,
            'is_done' => $this->when(
                $request->user()?->role === 'student' && $this->relationLoaded('completions'),
                fn (): bool => $this->completions->isNotEmpty(),
            ),
        ];
    }
}
