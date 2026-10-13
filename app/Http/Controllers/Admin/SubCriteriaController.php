<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCriteriaRequest;
use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Support\Facades\Gate;

class SubCriteriaController extends Controller
{
    public function store(StoreSubCriteriaRequest $request, Criteria $criteria)
    {
        Gate::authorize('create', SubCriteria::class);
        $criteria->subCriterias()->create($request->validated());

        return back()->with('success', 'Data sub kriteria berhasil ditambahkan');
    }
}
