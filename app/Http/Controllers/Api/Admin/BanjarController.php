<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banjar;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class BanjarController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Banjar::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        }

        $banjars = $query->paginate($request->per_page ?? 10);

        return $this->paginated($banjars);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $banjar = Banjar::create($validated);

        return $this->success($banjar, 201);
    }

    public function show($id)
    {
        $banjar = Banjar::withCount('residents')->find($id);

        if (!$banjar) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        return $this->success($banjar);
    }

    public function update(Request $request, $id)
    {
        $banjar = Banjar::find($id);

        if (!$banjar) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $banjar->update($validated);

        return $this->success($banjar);
    }

    public function destroy($id)
    {
        $banjar = Banjar::find($id);

        if (!$banjar) {
            return $this->error('RESOURCE_NOT_FOUND');
        }

        if ($banjar->residents()->exists()) {
            return $this->error('BANJAR_HAS_RESIDENTS');
        }

        $banjar->delete();

        return $this->success(null);
    }
}
