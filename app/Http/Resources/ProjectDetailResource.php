<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
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
            'description' => $this->description,
            'category' => $this->category->name ?? null,
            'main_image' => $this->main_image,
            'images' => $this->images,
            'features' => $this->features,
            'content' => $this->content,
            'videos' => $this->videos,
            'display_order' => $this->display_order,
        ];
    }
}
