<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ImageOptimizationService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Log;

class UserController extends Controller
{

    protected $imageService;
    protected $imageOptimizationService;

    public function __construct(ImageService $imageService, ImageOptimizationService $imageOptimizationService)
    {
        $this->imageService = $imageService;
        $this->imageOptimizationService = $imageOptimizationService;
    }

    /**
     * Get user List
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'count' => 'integer|min:1|max:100'
        ]);

        $page = $validated['page'] ?? 1;
        $count = $validated['count'] ?? 5;

        $usersQuery = User::orderBy('id', 'asc');
        $totalUsers = $usersQuery->count();
        $users = $usersQuery->skip(($page - 1) * $count)->take($count)->get();

        $nextPage = $totalUsers > $page * $count ? url('/api/v1/users?page=' . ($page + 1) . '&count=' . $count) : null;
        $prevPage = $page > 1 ? url('/api/v1/users?page=' . ($page - 1) . '&count=' . $count) : null;

        return response()->json([
            'success' => true,
            'page' => $page,
            'total_pages' => ceil($totalUsers / $count),
            'total_users' => $totalUsers,
            'count' => $count,
            'links' => [
                'next_url' => $nextPage,
                'prev_url' => $prevPage
            ],
            'users' => UserResource::collection($users)
        ]);
    }

    /**
     * Store new user
     * @param UserStoreRequest $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $imageName = null;

        $token = $request->input('token');

        $user = User::whereHas('tokens', function ($query) use ($token) {
            $query->where('id', $token);
        })->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid or expired token'], 400);
        }

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageName = Str::random(10) . '.jpg';
            $imagePath = 'public/images/';

            // Resize and save image
            $this->imageService->resizeAndSave($image, $imagePath, $imageName);

            // Optimize image
            $this->imageOptimizationService->optimize(storage_path('app/' . $imagePath . $imageName));
        }

        // Generate token
        $authToken = Str::random(60);

        // Create new user
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'photo'      => $imageName,
            'auth_token' => $authToken,
        ]);

        return new JsonResponse(new UserResource($user));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $user = User::with('position')->find($id);

        if ($user) {

            return response()->json([
                'success' => true,
                'user' => new UserResource($user)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
}
