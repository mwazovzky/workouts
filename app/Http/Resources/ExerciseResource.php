<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Exercise
 */
class ExerciseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? null,
            'description' => $this->description ?? null,
            'equipment_id' => $this->equipment_id,
            'effort_type' => $this->effort_type->value,
            'rest_time_seconds' => $this->rest_time_seconds,
            'category_ids' => $this->whenLoaded('categories', fn () => $this->categories->pluck('id')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'translations' => [
                'name' => $this->translationMap('name'),
                'description' => $this->translationMap('description'),
            ],
        ];
    }
}
