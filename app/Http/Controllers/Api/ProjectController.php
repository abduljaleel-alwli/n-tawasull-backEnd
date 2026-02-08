<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="Get all projects or filter by category",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of projects",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Create a base query to fetch projects
        $query = Project::query();

        // Filter by category if category_id is provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Fetch all projects with pagination
        $projects = $query->paginate(10);

        // Return projects as JSON using the ProjectResource for formatting
        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($projects)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{id}",
     *     summary="Show a single project",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project data",
     *         @OA\JsonContent(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     )
     * )
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project)
        ]);
    }

    /**
     * @OA\Schema(
     *     schema="Project",
     *     type="object",
     *     @OA\Property(property="title", type="string"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="category", type="string"),
     *     @OA\Property(property="main_image", type="string"),
     *     @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="features", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="content", type="string"),
     *     @OA\Property(property="videos", type="array", @OA\Items(type="object")),
     *     @OA\Property(property="is_active", type="boolean"),
     *     @OA\Property(property="display_order", type="integer"),
     * )
     */
}
