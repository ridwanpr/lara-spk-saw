<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetCondition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssetRequest;
use App\Http\Requests\Admin\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetController extends Controller
{
    /**
     * Display a listing of assets.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Asset::class);

        $search = $request->string('search')->trim()->value();
        $categoryId = $request->integer('category_id');
        $roomId = $request->integer('room_id');
        $condition = $request->string('condition')->trim()->value();

        $assets = Asset::query()
            ->with(['category', 'room'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($categoryId > 0, function ($query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($roomId > 0, function ($query) use ($roomId): void {
                $query->where('room_id', $roomId);
            })
            ->when($condition !== '', function ($query) use ($condition): void {
                $query->where('condition', $condition);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $categories = AssetCategory::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $conditions = AssetCondition::cases();

        return view('admin.assets.index', compact(
            'assets',
            'categories',
            'rooms',
            'conditions',
            'search',
            'categoryId',
            'roomId',
            'condition'
        ));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create(): View
    {
        Gate::authorize('create', Asset::class);

        $categories = AssetCategory::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $conditions = AssetCondition::cases();

        return view('admin.assets.create', compact('categories', 'rooms', 'conditions'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(StoreAssetRequest $request): RedirectResponse
    {
        Gate::authorize('create', Asset::class);

        Asset::create($request->validated());

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Data aset berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset): View
    {
        Gate::authorize('update', $asset);

        $categories = AssetCategory::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $conditions = AssetCondition::cases();

        return view('admin.assets.edit', compact('asset', 'categories', 'rooms', 'conditions'));
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('update', $asset);

        $asset->update($request->validated());

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Data aset berhasil diperbarui.');
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        Gate::authorize('delete', $asset);

        if ($asset->repairTickets()->exists()) {
            return redirect()
                ->route('admin.assets.index')
                ->with('error', 'Aset tidak dapat dihapus karena memiliki riwayat tiket perbaikan.');
        }

        $asset->delete();

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Data aset berhasil dihapus.');
    }
}
