<?php

namespace App\Http\Middleware;

use App\Models\Company\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    /**
     * Session sticky perusahaan (pengganti session `companyId` Symfony):
     * saat user memilih Perusahaan, ID disimpan di session sehingga CRUD turunan
     * (alamat perusahaan, departemen, dll.) otomatis tersaring.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $candidate = $request->route('company') ?? $request->input('company_id');

        if ($candidate !== null) {
            if (Company::whereKey($candidate)->exists()) {
                $request->session()->put('hris.company_id', $candidate);
            }
        }

        $companyId = $request->session()->get('hris.company_id');

        View::share('currentCompanyId', $companyId);
        View::share('currentCompany', $companyId ? Company::find($companyId) : null);

        return $next($request);
    }
}