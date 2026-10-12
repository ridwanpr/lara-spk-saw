<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetCategoryRequest;
use App\Http\Requests\Admin\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of asset categories.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AssetCategory::class);

        $search = $request->string('search')->trim()->value();

        $categories = AssetCategory::query()
            ->withCount('assets')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.asset-categories.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new asset category.
     */
    public function create(): View
    {
        Gate::authorize('create', AssetCategory::class);

        return view('admin.asset-categories.create');
    }

    /**
     * Store a newly created asset category in storage.
     */
    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('create', AssetCategory::class);

        AssetCategory::create($request->validated());

        return redirect()
            ->route('admin.asset-categories.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified asset category.
     */
    public function edit(AssetCategory $assetCategory): View
    {
        Gate::authorize('update', $assetCategory);

        return view('admin.asset-categories.edit', ['category' => $assetCategory]);
    }

    /**
     * Update the specified asset category in storage.
     */
    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory): RedirectResponse
    {
        Gate::authorize('update', $assetCategory);

        $assetCategory->update($request->validated());

        return redirect()
            ->route('admin.asset-categories.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    /**
     * Remove the specified asset category from storage.
     */
    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        Gate::authorize('delete', $assetCategory);

        if ($assetCategory->assets()->exists()) {
            return redirect()
                ->route('admin.asset-categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data aset.');
        }

        $assetCategory->delete();

        return redirect()
            ->route('admin.asset-categories.index')
            ->with('success', 'Kategori aset berhasil dihapus.');
    }
}
