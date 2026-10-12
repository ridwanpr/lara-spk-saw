<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Room::class);

        $search = $request->string('search')->trim()->value();

        $rooms = Room::query()
            ->withCount('assets')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rooms.index', compact('rooms', 'search'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create(): View
    {
        Gate::authorize('create', Room::class);

        return view('admin.rooms.create');
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Gate::authorize('create', Room::class);

        Room::create($request->validated());

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Data ruangan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room): View
    {
        Gate::authorize('update', $room);

        return view('admin.rooms.edit', compact('room'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        Gate::authorize('update', $room);

        $room->update($request->validated());

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        Gate::authorize('delete', $room);

        if ($room->assets()->exists()) {
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki data aset terkait.');
        }

        $room->delete();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Data ruangan berhasil dihapus.');
    }
}
