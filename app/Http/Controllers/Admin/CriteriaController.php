<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCriteriaRequest;
use App\Http\Requests\Admin\UpdateCriteriaRequest;
use App\Models\Criteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CriteriaController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Criteria::class);

        $criterias = Criteria::all();

        return view('admin.criterias.index', compact('criterias'));
    }

    public function create()
    {
        Gate::authorize('create', Criteria::class);

        return view('admin.criterias.create');
    }

    public function store(StoreCriteriaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Criteria::class);

        Criteria::create($request->validated());

        return redirect()->route('admin.criterias.index')
            ->with('success', 'Data kriteria berhasil ditambahkan');
    }

    public function destroy(Criteria $criteria): RedirectResponse
    {
        Gate::authorize('delete', $criteria);

        $criteria->delete();

        return redirect()
            ->route('admin.criterias.index')
            ->with('success', 'Kriteria berhasil dihapus.');
    }

    public function show(Criteria $criteria)
    {
        $criteria->load(['subCriterias']);

        return view('admin.criterias.show', compact('criteria'));
    }

    public function update(UpdateCriteriaRequest $request, Criteria $criteria)
    {
        Gate::authorize('update', $criteria);

        $criteria->update($request->validated());

        return back()->with('success', 'Kriteria berhasil diupdate');
    }
}
