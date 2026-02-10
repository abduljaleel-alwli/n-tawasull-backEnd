<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/settings",
     *     summary="Get all settings or filter by group",
     *     tags={"Settings"},
     *     @OA\Parameter(
     *         name="group",
     *         in="query",
     *         description="Filter by group",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of settings",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Setting")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function getSettings(Request $request)
    {
        // If a group is provided in the query parameters, filter by group
        $group = $request->query('group', null);

        $query = Setting::query();

        if ($group) {
            $query->where('group', $group); // Filter by group
        }

        $settings = $query->get();

        // Iterate through each setting and clean the value if necessary
        foreach ($settings as $setting) {
            // If the value is a JSON string (e.g., for 'contact.social_links', 'hero.ctas')
            if ($setting->type == 'json' && $setting->value) {
                // Decode the JSON string to a PHP array
                $decodedValue = json_decode($setting->value, true);

                // If the value is a valid JSON array, clean the URLs and decode Unicode
                if ($decodedValue) {
                    array_walk_recursive($decodedValue, function (&$item) {
                        if (is_string($item)) {
                            // Decode any unicode escape sequences
                            $item = $this->decodeUnicode($item);

                            // Replace encoded slashes
                            $item = str_replace('\/', '/', $item);
                        }
                    });

                    // Re-encode the cleaned value back to JSON without escaping slashes
                    $setting->value = json_encode($decodedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            } else {
                // If it's a regular value, decode Unicode (for string values)
                $setting->value = $this->decodeUnicode($setting->value);
            }

            $setting->makeHidden(['id', 'created_at', 'updated_at']);
        }

        // Return the data in a structured response
        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $settings
        ]);
    }

    /**
     * Decode Unicode escape sequences in a string (e.g., \u0645\u0645\u0646\u0633\u064a\u062a)
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

    /**
     * @OA\Get(
     *     path="/api/settings/{key}",
     *     summary="Get a specific setting by its key",
     *     tags={"Settings"},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="The key of the setting",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Setting data",
     *         @OA\JsonContent(ref="#/components/schemas/Setting")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Setting not found"
     *     )
     * )
     */
    public function getSettingByKey($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.'
            ], 404);
        }

        // Check if the value is a JSON string and clean the URLs and Unicode
        if ($setting->type == 'json' && $setting->value) {
            $decodedValue = json_decode($setting->value, true);

            if ($decodedValue) {
                array_walk_recursive($decodedValue, function (&$item) {
                    if (is_string($item)) {
                        // Decode any unicode escape sequences
                        $item = $this->decodeUnicode($item);

                        // Replace encoded slashes
                        $item = str_replace('\/', '/', $item);
                    }
                });

                // Re-encode the cleaned value back to JSON without escaping slashes
                $setting->value = json_encode($decodedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        } else {
            // Decode Unicode in regular values (for string values)
            $setting->value = $this->decodeUnicode($setting->value);
        }

        // Hide sensitive fields (including 'id')
        $setting->makeHidden(['id', 'created_at', 'updated_at']);

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $setting
        ]);
    }


    /**
     * @OA\Schema(
     *     schema="Setting",
     *     type="object",
     *     @OA\Property(property="key", type="string"),
     *     @OA\Property(property="value", type="string"),
     *     @OA\Property(property="type", type="string", enum={"json", "string"}),
     *     @OA\Property(property="group", type="string"),
     * )
     */
}
