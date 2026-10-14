<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCriteriaRequest;
use App\Http\Requests\Admin\UpdateSubCriteriaRequest;
use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Support\Facades\Gate;

class SubCriteriaController extends Controller
{
    public function store(StoreSubCriteriaRequest $request, Criteria $criteria)
    {
        Gate::authorize('create', SubCriteria::class);
        $criteria->subCriterias()->create($request->validated());

        return back()
            ->with('success', 'Data sub kriteria berhasil ditambahkan');
    }

    public function update(UpdateSubCriteriaRequest $request, SubCriteria $subCriteria)
    {
        Gate::authorize('update', $subCriteria);

        $validated = $request->validated();
        $subCriteria->update([
            'description' => $validated['edit_description'],
            'crisp_value' => $validated['edit_crisp_value'],
            'min_value' => $validated['edit_min_value'] ?? null,
            'max_value' => $validated['edit_max_value'] ?? null,
        ]);

        return back()
            ->with('success', 'Data sub kriteria berhasil diubah');
    }

    public function destroy(SubCriteria $subCriteria)
    {
        Gate::authorize('delete', $subCriteria);

        $subCriteria->delete();

        return back()
            ->with('success', 'Data sub kriteria berhasil dihapus');
    }
}
