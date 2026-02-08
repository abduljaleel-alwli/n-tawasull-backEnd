<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Get all services or filter by category or active status",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of services",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function getServices(Request $request)
    {
        // Initialize the query
        $query = Service::query();

        // Filter by category_id if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Apply pagination
        $services = $query->paginate(10);  // 10 items per page, you can adjust this as needed

        // Return the data in a structured response
        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($services)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Get a specific service by its ID",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service data",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function getServiceById($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
        }

        // Return the transformed service
        return response()->json([
            'success' => true,
            'data' => new ServiceResource($service)
        ]);
    }

    /**
     * @OA\Schema(
     *     schema="Service",
     *     type="object",
     *     @OA\Property(property="title", type="string"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="category", type="string"),
     *     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="main_image", type="string"),
     *     @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="is_active", type="boolean"),
     *     @OA\Property(property="display_order", type="integer"),
     * )
     */
}
