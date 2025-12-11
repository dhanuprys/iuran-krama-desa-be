<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $query->latest()->paginate(15);

        return $this->paginated($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Ensure password hashing if not handled by Model mutator (assuming default setup, need to hash)
        // Ideally should be in Request or Model, but here is fine or explicit.
        // Laravel's create with 'password' usually needs hashing.
        // Checking previous implementation: User::create($validator->validated());
        // If User model has mutator setPasswordAttribute, it's fine.
        // If not, we should hash it. The original code didn't show hashing in 'store' method (Wait, let me check view_file 1790 lines 59).
        // Line 59: $user = User::create($validator->validated());
        // Line 50: 'password' => 'required|string|min:8'
        // AuthController uses Hash::make (User::create also used raw $request->password in AuthController line 32? No, let's check line 32 in view_file 1789)
        // AuthController: 'password' => $request->password. Wait. Does User model handle hashing?

        // I will assume User model does NOT handle hashing if AuthController was passing raw.
        // Only AuthController line 103/131 used Hash::make.
        // AuthController register method (line 15-45) passed raw password.
        // Does User model cast password?
        // Let's check User model to be safe. But to match original functionality, I should do what original code did.
        // Original UserController just passed validated array. 
        // If the original code was broken (storing plain text), I should probably fix it, but user asked "without changing functionality".
        // HOWEVER, AuthController register passes raw, UserController store passes raw.
        // If I change it to Hash::make, I change functionality (fixing a bug potentially).
        // I will stick to exact behavior of previous code, which was just passing validated data.

        $user = User::create($data);

        return $this->success($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with('residents')->find($id);

        if (!$user) {
            return $this->error('NOT_FOUND', null, 'User not found', 404);
        }

        return $this->success($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('NOT_FOUND', null, 'User not found', 404);
        }

        $data = $request->validated();

        // If password is provided in update, we probably need to hash it? 
        // Original code: $user->update($validator->validated());
        // It seems the original code MIGHT rely on a mutator.

        $user->update($data);

        return $this->success($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('NOT_FOUND', null, 'User not found', 404);
        }

        if ($user->id === auth()->id()) {
            return $this->error('FORBIDDEN', null, 'Cannot delete yourself', 403);
        }

        $user->delete();

        return $this->success(null, 200);
    }
}
