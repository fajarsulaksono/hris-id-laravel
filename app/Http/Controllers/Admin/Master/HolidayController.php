<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\BaseController;
use App\Models\Master\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends BaseController
{
    public function index(Request $request): View
    {
        $holidays = $this->paginate(
            Holiday::query()->latest('holiday_date')
        );

        return view('admin.master.holiday.index', ['holidays' => $holidays]);
    }

    public function create(): View
    {
        return view('admin.master.holiday.form', ['holiday' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Holiday::create($request->validate($this->rules()));

        return redirect()->route('admin.master.holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function show(string $holiday): View
    {
        $holiday = $this->findOrFail(Holiday::class, $holiday);

        return view('admin.master.holiday.show', ['holiday' => $holiday]);
    }

    public function edit(string $holiday): View
    {
        $holiday = $this->findOrFail(Holiday::class, $holiday);

        return view('admin.master.holiday.form', ['holiday' => $holiday]);
    }

    public function update(Request $request, string $holiday): RedirectResponse
    {
        $holiday = $this->findOrFail(Holiday::class, $holiday);

        $holiday->update($request->validate($this->rules($holiday->id)));

        return redirect()->route('admin.master.holidays.index')
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(string $holiday): RedirectResponse
    {
        $holiday = $this->findOrFail(Holiday::class, $holiday);

        $holiday->delete();

        return redirect()->route('admin.master.holidays.index')
            ->with('success', 'Hari libur dihapus.');
    }

    protected function rules(?string $ignoreId = null): array
    {
        return [
            'holiday_date' => ['required', 'date', 'unique:holidays,holiday_date,'.($ignoreId ?? 'NULL').',id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}