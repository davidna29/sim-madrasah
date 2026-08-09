<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Madrasah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MadrasahProfileController extends Controller
{
    /**
     * Menampilkan form identitas madrasah.
     */
    public function edit(): View
    {
        $madrasah = Madrasah::query()
            ->where('code', 'default')
            ->firstOrFail();

        return view('admin.madrasah.edit', [
            'madrasah' => $madrasah,
        ]);
    }

    /**
     * Menyimpan perubahan identitas madrasah.
     */
    public function update(Request $request): RedirectResponse
    {
        $madrasah = Madrasah::query()
            ->where('code', 'default')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'nsm' => [
                'nullable',
                'string',
                'max:30',
            ],
            'npsn' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:191',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'village' => [
                'nullable',
                'string',
                'max:100',
            ],
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'province' => [
                'nullable',
                'string',
                'max:100',
            ],
            'postal_code' => [
                'nullable',
                'string',
                'max:10',
            ],
            'timezone' => [
                'required',
                'string',
                'max:50',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $madrasah->update($validated);

        return redirect()
            ->route('admin.madrasah.edit')
            ->with('success', 'Identitas madrasah berhasil diperbarui.');
    }
}
