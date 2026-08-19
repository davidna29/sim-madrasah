<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleTemplate;
use App\Models\ScheduleTemplateSlot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScheduleTemplateSlotController extends Controller
{
    private const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    private const SLOT_TYPES = [
        'kbm' => 'Belajar Mengajar',
        'istirahat' => 'Istirahat',
        'upacara' => 'Upacara',
        'kegiatan_rutin' => 'Kegiatan Rutin',
    ];

    public function index(ScheduleTemplate $scheduleTemplate): View
    {
        $slots = $scheduleTemplate->slots()
            ->orderBy('day_of_week')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('day_of_week');

        return view('admin.schedule-templates.slots.index', [
            'scheduleTemplate' => $scheduleTemplate,
            'slots' => $slots,
            'days' => self::DAYS,
            'slotTypes' => self::SLOT_TYPES,
            'canManageSlots' => $this->canManageSlots($scheduleTemplate),
        ]);
    }

    public function create(ScheduleTemplate $scheduleTemplate): View|RedirectResponse
    {
        if (! $this->canManageSlots($scheduleTemplate)) {
            return $this->redirectWhenTemplateLocked($scheduleTemplate);
        }

        return view('admin.schedule-templates.slots.create', [
            'scheduleTemplate' => $scheduleTemplate,
            'slot' => new ScheduleTemplateSlot([
                'day_of_week' => collect($scheduleTemplate->active_days)->first(),
                'sort_order' => 1,
                'slot_type' => 'kbm',
                'is_teaching_slot' => true,
            ]),
            'days' => $this->activeDaysForTemplate($scheduleTemplate),
            'slotTypes' => self::SLOT_TYPES,
        ]);
    }

    public function store(
        Request $request,
        ScheduleTemplate $scheduleTemplate
    ): RedirectResponse {
        if (! $this->canManageSlots($scheduleTemplate)) {
            return $this->redirectWhenTemplateLocked($scheduleTemplate);
        }

        $validated = $this->validateSlot($request, $scheduleTemplate);

        $scheduleTemplate->slots()->create($validated);

        return redirect()
            ->route('admin.schedule-templates.slots.index', $scheduleTemplate)
            ->with('success', 'Slot template jadwal berhasil dibuat.');
    }

    public function edit(ScheduleTemplateSlot $scheduleTemplateSlot): View|RedirectResponse
    {
        $scheduleTemplate = $scheduleTemplateSlot->scheduleTemplate;

        if (! $this->canManageSlots($scheduleTemplate)) {
            return $this->redirectWhenTemplateLocked($scheduleTemplate);
        }

        return view('admin.schedule-templates.slots.edit', [
            'scheduleTemplate' => $scheduleTemplate,
            'slot' => $scheduleTemplateSlot,
            'days' => $this->activeDaysForTemplate($scheduleTemplate),
            'slotTypes' => self::SLOT_TYPES,
        ]);
    }

    public function update(
        Request $request,
        ScheduleTemplateSlot $scheduleTemplateSlot
    ): RedirectResponse {
        $scheduleTemplate = $scheduleTemplateSlot->scheduleTemplate;

        if (! $this->canManageSlots($scheduleTemplate)) {
            return $this->redirectWhenTemplateLocked($scheduleTemplate);
        }

        $validated = $this->validateSlot(
            $request,
            $scheduleTemplate,
            $scheduleTemplateSlot
        );

        $scheduleTemplateSlot->update($validated);

        return redirect()
            ->route('admin.schedule-templates.slots.index', $scheduleTemplate)
            ->with('success', 'Slot template jadwal berhasil diperbarui.');
    }

    public function destroy(ScheduleTemplateSlot $scheduleTemplateSlot): RedirectResponse
    {
        $scheduleTemplate = $scheduleTemplateSlot->scheduleTemplate;

        if (! $this->canManageSlots($scheduleTemplate)) {
            return $this->redirectWhenTemplateLocked($scheduleTemplate);
        }

        $scheduleTemplateSlot->delete();

        return redirect()
            ->route('admin.schedule-templates.slots.index', $scheduleTemplate)
            ->with('success', 'Slot template jadwal berhasil dihapus.');
    }

    /**
     * Validasi slot template jadwal.
     *
     * @return array<string, mixed>
     */
    private function validateSlot(
        Request $request,
        ScheduleTemplate $scheduleTemplate,
        ?ScheduleTemplateSlot $slot = null
    ): array {
        $activeDayValues = array_keys($this->activeDaysForTemplate($scheduleTemplate));

        $validator = Validator::make($request->all(), [
            'day_of_week' => [
                'required',
                'integer',
                Rule::in($activeDayValues),
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                'max:'.$scheduleTemplate->max_slots_per_day,
            ],
            'starts_at' => [
                'required',
                'date_format:H:i',
            ],
            'ends_at' => [
                'required',
                'date_format:H:i',
            ],
            'slot_type' => [
                'required',
                Rule::in(array_keys(self::SLOT_TYPES)),
            ],
            'label' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validator->after(function ($validator) use ($request, $scheduleTemplate, $slot): void {
            $dayOfWeek = (int) $request->input('day_of_week');
            $sortOrder = (int) $request->input('sort_order');
            $startsAt = (string) $request->input('starts_at');
            $endsAt = (string) $request->input('ends_at');

            if ($startsAt !== '' && $endsAt !== '' && $startsAt >= $endsAt) {
                $validator->errors()->add(
                    'ends_at',
                    'Jam selesai harus lebih besar dari jam mulai.'
                );
            }

            if ($this->hasDuplicateSortOrder(
                $scheduleTemplate,
                $dayOfWeek,
                $sortOrder,
                $slot
            )) {
                $validator->errors()->add(
                    'sort_order',
                    'Nomor urut slot pada hari tersebut sudah digunakan.'
                );
            }

            if (
                $startsAt !== ''
                && $endsAt !== ''
                && $startsAt < $endsAt
                && $this->hasOverlappingTime($scheduleTemplate, $dayOfWeek, $startsAt, $endsAt, $slot)
            ) {
                $validator->errors()->add(
                    'starts_at',
                    'Jam slot bertabrakan dengan slot lain pada hari yang sama.'
                );
            }
        });

        $validated = $validator->validate();

        $validated['day_of_week'] = (int) $validated['day_of_week'];
        $validated['sort_order'] = (int) $validated['sort_order'];
        $validated['starts_at'] = substr($validated['starts_at'], 0, 5);
        $validated['ends_at'] = substr($validated['ends_at'], 0, 5);
        $validated['is_teaching_slot'] = $validated['slot_type'] === 'kbm';

        return $validated;
    }

    private function hasDuplicateSortOrder(
        ScheduleTemplate $scheduleTemplate,
        int $dayOfWeek,
        int $sortOrder,
        ?ScheduleTemplateSlot $slot = null
    ): bool {
        return $scheduleTemplate->slots()
            ->where('day_of_week', $dayOfWeek)
            ->where('sort_order', $sortOrder)
            ->when($slot, fn ($query) => $query->whereKeyNot($slot->id))
            ->exists();
    }

    private function hasOverlappingTime(
        ScheduleTemplate $scheduleTemplate,
        int $dayOfWeek,
        string $startsAt,
        string $endsAt,
        ?ScheduleTemplateSlot $slot = null
    ): bool {
        return $scheduleTemplate->slots()
            ->where('day_of_week', $dayOfWeek)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($slot, fn ($query) => $query->whereKeyNot($slot->id))
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    private function activeDaysForTemplate(ScheduleTemplate $scheduleTemplate): array
    {
        $activeDays = collect($scheduleTemplate->active_days ?? [])
            ->map(fn ($day): int => (int) $day)
            ->filter(fn (int $day): bool => array_key_exists($day, self::DAYS))
            ->unique()
            ->sort()
            ->values();

        if ($activeDays->isEmpty()) {
            return self::DAYS;
        }

        return $activeDays
            ->mapWithKeys(fn (int $day): array => [$day => self::DAYS[$day]])
            ->all();
    }

    private function canManageSlots(ScheduleTemplate $scheduleTemplate): bool
    {
        return ! $scheduleTemplate->classGroupAssignments()->exists();
    }

    private function redirectWhenTemplateLocked(
        ScheduleTemplate $scheduleTemplate
    ): RedirectResponse {
        return redirect()
            ->route('admin.schedule-templates.slots.index', $scheduleTemplate)
            ->with('error', 'Slot template tidak dapat diubah karena template sudah dipakai oleh rombel.');
    }
}
