<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProjectSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => Str::limit($this->description, 100),
            'category_id' => $this->category->id ?? null, // Return category ID for filtering
            'category' => $this->category->name ?? null, // Return category name for filtering
            'main_image' => $this->main_image,
            'display_order' => $this->display_order,
        ];
    }
}
