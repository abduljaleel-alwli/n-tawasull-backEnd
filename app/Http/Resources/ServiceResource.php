<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // Decode JSON fields if they exist
        $tags = $this->decodeJson($this->tags);
        $images = $this->decodeJson($this->images);

        // Return the transformed data
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category ? $this->category->name : null, // Display category name
            'tags' => $tags,
            'main_image' => $this->main_image,
            'images' => $images,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
        ];
    }

    /**
     * Decode and fix Unicode escape sequences in a JSON string.
     *
     * @param mixed $json
     * @return mixed
     */
    private function decodeJson($json)
    {
        // Only attempt to decode if the value is a string
        if (is_string($json)) {
            $decoded = json_decode($json, true);
            if ($decoded) {
                array_walk_recursive($decoded, function (&$item) {
                    if (is_string($item)) {
                        // Decode Unicode escape sequences (e.g., \u0645\u0645\u0646\u0633\u064A\u062A)
                        $item = $this->decodeUnicode($item);
                        
                        // Replace encoded slashes
                        $item = str_replace('\/', '/', $item);
                    }
                });
                return $decoded;
            }
        }

        // If it's already an array or not a valid JSON, return it as is
        return $json;
    }

    /**
     * Decode Unicode escape sequences in a string (e.g., \u0645\u0645\u0646\u0633\u064A\u062A).
     *
     * @param string $string
     * @return string
     */
    private function decodeUnicode($string)
    {
        // Decode any unicode escape sequences
        return preg_replace_callback('/\\\u([0-9a-fA-F]{4})/', function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2');
        }, $string);
    }
}
