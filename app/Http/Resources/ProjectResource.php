<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category ? $this->category->name : null, // Display category name
            'main_image' => $this->main_image,
            'images' => $this->images,
            'features' => $this->features,
            'content' => $this->content,
            'videos' => $this->videos,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
        ];
    }
}
