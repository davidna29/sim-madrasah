<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ScheduleTemplateController extends Controller
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

    public function index(): View
    {
        $scheduleTemplates = ScheduleTemplate::query()
            ->withCount([
                'slots',
                'classGroupAssignments',
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.schedule-templates.index', [
            'scheduleTemplates' => $scheduleTemplates,
            'days' => self::DAYS,
        ]);
    }

    public function create(): View
    {
        return view('admin.schedule-templates.create', [
            'scheduleTemplate' => new ScheduleTemplate([
                'active_days' => [1, 2, 3, 4, 5],
                'holiday_days' => [6, 7],
                'max_slots_per_day' => 10,
                'standard_slot_duration_minutes' => 35,
                'status' => 'draft',
                'is_active' => true,
            ]),
            'days' => self::DAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateScheduleTemplate($request);

        $validated['created_by'] = $request->user()?->id;

        ScheduleTemplate::create($validated);

        return redirect()
            ->route('admin.schedule-templates.index')
            ->with('success', 'Template jadwal berhasil dibuat.');
    }

    public function edit(ScheduleTemplate $scheduleTemplate): View
    {
        return view('admin.schedule-templates.edit', [
            'scheduleTemplate' => $scheduleTemplate,
            'days' => self::DAYS,
        ]);
    }

    public function update(
        Request $request,
        ScheduleTemplate $scheduleTemplate
    ): RedirectResponse {
        $validated = $this->validateScheduleTemplate($request, $scheduleTemplate);

        $scheduleTemplate->update($validated);

        return redirect()
            ->route('admin.schedule-templates.index')
            ->with('success', 'Template jadwal berhasil diperbarui.');
    }

    public function clone(ScheduleTemplate $scheduleTemplate): RedirectResponse
    {
        DB::transaction(function () use ($scheduleTemplate): void {
            $copy = $scheduleTemplate->replicate([
                'code',
                'name',
                'created_by',
            ]);

            $copy->code = $this->makeUniqueCopyCode($scheduleTemplate->code);
            $copy->name = $scheduleTemplate->name.' - Salinan';
            $copy->status = 'draft';
            $copy->is_active = false;
            $copy->created_by = request()->user()?->id;
            $copy->save();

            $scheduleTemplate->slots()
                ->orderBy('day_of_week')
                ->orderBy('sort_order')
                ->get()
                ->each(function ($slot) use ($copy): void {
                    $copy->slots()->create(
                        Arr::only($slot->getAttributes(), [
                            'day_of_week',
                            'sort_order',
                            'starts_at',
                            'ends_at',
                            'slot_type',
                            'label',
                            'is_teaching_slot',
                            'notes',
                        ])
                    );
                });
        });

        return redirect()
            ->route('admin.schedule-templates.index')
            ->with('success', 'Template jadwal berhasil digandakan.');
    }

    public function destroy(ScheduleTemplate $scheduleTemplate): RedirectResponse
    {
        if ($scheduleTemplate->is_active) {
            return redirect()
                ->route('admin.schedule-templates.index')
                ->with('error', 'Template jadwal aktif tidak boleh dihapus. Nonaktifkan dulu template tersebut.');
        }

        if ($scheduleTemplate->classGroupAssignments()->exists()) {
            return redirect()
                ->route('admin.schedule-templates.index')
                ->with('error', 'Template jadwal tidak boleh dihapus karena sudah dipakai oleh rombel.');
        }

        $scheduleTemplate->delete();

        return redirect()
            ->route('admin.schedule-templates.index')
            ->with('success', 'Template jadwal berhasil dihapus.');
    }

    /**
     * Validasi input template jadwal.
     *
     * @return array<string, mixed>
     */
    private function validateScheduleTemplate(
        Request $request,
        ?ScheduleTemplate $scheduleTemplate = null
    ): array {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schedule_templates', 'code')->ignore($scheduleTemplate),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'active_days' => [
                'required',
                'array',
                'min:1',
            ],
            'active_days.*' => [
                'integer',
                Rule::in(array_keys(self::DAYS)),
            ],
            'holiday_days' => [
                'nullable',
                'array',
            ],
            'holiday_days.*' => [
                'integer',
                Rule::in(array_keys(self::DAYS)),
            ],
            'max_slots_per_day' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],
            'standard_slot_duration_minutes' => [
                'required',
                'integer',
                'min:10',
                'max:120',
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'ready', 'archived']),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $activeDays = $this->normalizeDays($request->input('active_days', []));
            $holidayDays = $this->normalizeDays($request->input('holiday_days', []));

            if (array_intersect($activeDays, $holidayDays) !== []) {
                $validator->errors()->add(
                    'holiday_days',
                    'Hari libur tidak boleh sama dengan hari aktif.'
                );
            }
        });

        $validated = $validator->validate();

        $validated['active_days'] = $this->normalizeDays($validated['active_days'] ?? []);
        $validated['holiday_days'] = $this->normalizeDays($validated['holiday_days'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Merapikan nilai hari agar unik, angka, dan urut.
     *
     * @param  array<int, mixed>  $days
     * @return array<int, int>
     */
    private function normalizeDays(array $days): array
    {
        $normalizedDays = collect($days)
            ->map(fn ($day): int => (int) $day)
            ->filter(fn (int $day): bool => array_key_exists($day, self::DAYS))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $normalizedDays;
    }

    private function makeUniqueCopyCode(string $originalCode): string
    {
        $baseCode = Str::limit($originalCode, 40, '').'-COPY';
        $code = $baseCode;
        $counter = 2;

        while (ScheduleTemplate::query()->where('code', $code)->exists()) {
            $code = Str::limit($baseCode, 44, '').'-'.$counter;
            $counter++;
        }

        return $code;
    }
}
