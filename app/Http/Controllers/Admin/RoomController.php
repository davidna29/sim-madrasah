<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::query()
            ->orderBy('room_type')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): View
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRoom($request);

        Room::create($validated);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dibuat.');
    }

    public function toggleActive(Room $room): RedirectResponse
    {
        $room->update([
            'is_active' => ! $room->is_active,
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', $room->is_active
                ? 'Ruangan berhasil diaktifkan.'
                : 'Ruangan berhasil dinonaktifkan.');
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.edit', [
            'room' => $room,
        ]);
    }

    public function update(
        Request $request,
        Room $room
    ): RedirectResponse {
        $validated = $this->validateRoom(
            $request,
            $room
        );

        $room->update($validated);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    /**
     * Validasi ruangan.
     *
     * @return array<string, mixed>
     */
    private function validateRoom(
        Request $request,
        ?Room $room = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('rooms', 'code')
                    ->ignore($room),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'room_type' => [
                'required',
                'string',
                'max:50',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'location' => [
                'nullable',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
