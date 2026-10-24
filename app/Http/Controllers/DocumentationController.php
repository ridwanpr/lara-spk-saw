<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use Illuminate\Contracts\View\View;

class DocumentationController extends Controller
{
    /**
     * Tampilkan halaman dokumentasi aplikasi.
     */
    public function __invoke(): View
    {
        $criterias = Criteria::with('subCriterias')->orderBy('code')->get();

        return view('documentation', compact('criterias'));
    }
}
