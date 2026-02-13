<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Resources\ProjectSummaryResource;
use App\Http\Resources\ProjectDetailResource;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="Retrieve list of projects",
     *     description="Returns a (optionally filtered) list of projects. Supports filtering by category, free-text search, pagination and sorting.",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter results to projects belonging to the specified category ID",
     *         required=false,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Free-text search applied to project title and description",
     *         required=false,
     *         @OA\Schema(type="string", example="community center")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for paginated results",
     *         required=false,
     *         @OA\Schema(type="integer", example=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by (e.g. created_at, title)",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort direction: asc or desc",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of projects (paginated)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Project")),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request — invalid query parameters"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No projects found matching the specified criteria"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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

        // تصفية المشاريع المفعلة فقط (حيث is_active = true)
        $query->where('is_active', true);

        // Fetch all projects with pagination
        $projects = $query->paginate(6);

        // Return projects as JSON using the ProjectSummaryResource for formatting
        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => ProjectSummaryResource::collection($projects)
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

        // التأكد من أن المشروع مفعّل فقط
        if (!$project->is_active) {
            return response()->json([
                'code' => 404,
                'status' => 'Not Found',
                'message' => 'Project not found or is inactive'
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => new ProjectDetailResource($project)
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
     *     @OA\Property(property="display_order", type="integer"),
     * )
     */
}
