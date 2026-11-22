<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleBatch;
use App\Models\ShiftRequirement;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Heuristik penjadwalan. TIDAK mengklaim optimum global.
 * Kendala wajib: staf aktif, status tersedia pada tanggal mulai,
 * tidak bentrok dengan draft/publikasi lain (termasuk lintas tengah malam).
 * Kendala istirahat minimum, perpindahan lokasi, dan jam kerja maksimum
 * perlu disepakati stakeholder sebelum dipakai dalam operasional.
 */
class SchedulingService
{
    public function bounds(string $date, string $startTime, string $endTime): array
    {
        $start = CarbonImmutable::parse("{$date} {$startTime}");
        $end = CarbonImmutable::parse("{$date} {$endTime}");
        if ($end->lessThanOrEqualTo($start)) {
            $end = $end->addDay();
        }
        return [$start, $end];
    }

    public function overlaps(string $dateA, object $shiftA, string $dateB, object $shiftB): bool
    {
        [$startA, $endA] = $this->bounds($dateA, $shiftA->start_time, $shiftA->end_time);
        [$startB, $endB] = $this->bounds($dateB, $shiftB->start_time, $shiftB->end_time);
        return $startA->lessThan($endB) && $startB->lessThan($endA);
    }

    /** Ketersediaan harian terpisah dari kesibukan akibat penugasan. */
    public function isAvailable(Staff $staff, string $date, object $shift, Collection $availability): bool
    {
        if (!$staff->is_active || ($availability->get("{$staff->id}|{$date}") !== 'available')) {
            return false;
        }
        [$start, $end] = $this->bounds($date, $shift->start_time, $shift->end_time);
        if ($end->toDateString() !== $start->toDateString()) {
            $next = $availability->get("{$staff->id}|{$end->toDateString()}");
            if (in_array($next, ['leave', 'unavailable'], true)) {
                return false;
            }
        }
        return true;
    }

    public function hasConflict(int $staffId, string $date, object $shift, Collection $assignments, ?int $excludeId = null): bool
    {
        foreach ($assignments as $assignment) {
            if ((int) $assignment->staff_id !== $staffId || (int) $assignment->id === $excludeId || $assignment->status === 'cancelled') {
                continue;
            }
            if ($this->overlaps($date, $shift, $assignment->schedule_date->toDateString(), $assignment->shift)) {
                return true;
            }
        }
        return false;
    }

    public function scheduleContext(string $date, bool $lock = false): array
    {
        $nearbyQuery = ScheduleAssignment::with('shift')
            ->whereBetween('schedule_date', [CarbonImmutable::parse($date)->subDay()->toDateString(), CarbonImmutable::parse($date)->addDay()->toDateString()])
            ->whereIn('status', ['draft', 'published']);
        $assignments = $lock ? $nearbyQuery->lockForUpdate()->get() : $nearbyQuery->get();
        $availability = Availability::whereBetween('date', [$date, CarbonImmutable::parse($date)->addDay()->toDateString()])
            ->get()->mapWithKeys(fn ($a) => ["{$a->staff_id}|{$a->date->toDateString()}" => $a->status]);
        return [$assignments, $availability];
    }

    /**
     * Generate sebagian: shortage tidak membatalkan penugasan yang berhasil.
     * Generate ulang mengisi kekosongan, tidak menimpa draft/publikasi lama.
     */
    public function generate(string $date, ?int $locationId, int $adminId): ScheduleBatch
    {
        return DB::transaction(function () use ($date, $locationId, $adminId) {
            // Penguncian staf mencegah dua proses generate bersamaan memilih staf yang sama di MySQL.
            $staff = Staff::where('is_active', true)->orderBy('id')->lockForUpdate()->get();
            $requirements = ShiftRequirement::with(['location', 'shift'])
                ->when($locationId, fn ($query) => $query->where('location_id', $locationId))
                ->where('required_count', '>', 0)
                ->get()->filter(fn ($r) => $r->location?->is_active && $r->shift?->is_active);
            if ($requirements->isEmpty()) {
                throw ValidationException::withMessages(['requirements' => 'Belum ada kebutuhan personel aktif. Isi menu Kebutuhan Shift terlebih dahulu.']);
            }
            [$assignments, $availability] = $this->scheduleContext($date, true);
            $workload = ScheduleAssignment::query()->select('staff_id')
                ->selectRaw('COUNT(*) AS total')
                ->whereIn('status', ['draft', 'published'])
                ->whereBetween('schedule_date', [CarbonImmutable::parse($date)->subDays(30)->toDateString(), CarbonImmutable::parse($date)->addDays(30)->toDateString()])
                ->groupBy('staff_id')->pluck('total', 'staff_id');
            $batch = ScheduleBatch::create([
                'schedule_date' => $date, 'location_id' => $locationId, 'generated_by' => $adminId,
                'required_count' => 0, 'created_count' => 0, 'shortage_count' => 0,
            ]);
            $createdCount = 0;
            $shortage = 0;
            $shortageNotes = [];

            // Prioritaskan posisi dengan kandidat paling sedikit terlebih dahulu.
            $requirements = $requirements->sortBy(function ($r) use ($date, $staff, $availability, $assignments) {
                return $staff->filter(fn ($s) => $this->isAvailable($s, $date, $r->shift, $availability)
                    && !$this->hasConflict($s->id, $date, $r->shift, $assignments))->count();
            });
            foreach ($requirements as $requirement) {
                $needed = (int) $requirement->required_count;
                $alreadyFilled = $assignments->filter(fn ($a) => $a->schedule_date->toDateString() === $date
                    && (int) $a->location_id === (int) $requirement->location_id
                    && (int) $a->shift_id === (int) $requirement->shift_id)->count();
                $openSlots = max(0, $needed - $alreadyFilled);
                $batch->required_count += $needed;
                for ($slot = 0; $slot < $openSlots; $slot++) {
                    $candidate = $staff->filter(fn ($s) => $this->isAvailable($s, $date, $requirement->shift, $availability)
                        && !$this->hasConflict($s->id, $date, $requirement->shift, $assignments))
                        ->sortBy(fn ($s) => sprintf('%08d-%08d', (int) ($workload[$s->id] ?? 0), $s->id))
                        ->first();
                    if (!$candidate) {
                        $missing = $openSlots - $slot;
                        $shortage += $missing;
                        $shortageNotes[] = "{$requirement->location->name} / {$requirement->shift->name}: kurang {$missing} staf";
                        break; // lanjut kebutuhan lokasi/shift berikutnya
                    }
                    $assignment = ScheduleAssignment::create([
                        'batch_id' => $batch->id, 'schedule_date' => $date,
                        'location_id' => $requirement->location_id, 'shift_id' => $requirement->shift_id,
                        'staff_id' => $candidate->id, 'status' => 'draft',
                    ]);
                    $assignment->setRelation('shift', $requirement->shift);
                    $assignments->push($assignment);
                    $workload[$candidate->id] = (int) ($workload[$candidate->id] ?? 0) + 1;
                    $createdCount++;
                }
            }
            $batch->created_count = $createdCount;
            $batch->shortage_count = $shortage;
            $batch->notes = $shortageNotes ? implode("\n", $shortageNotes) : 'Semua kebutuhan yang diperiksa sudah terisi.';
            $batch->save();
            return $batch;
        }, 3);
    }

    public function verifyAssignment(ScheduleAssignment $item, Collection $assignments, Collection $availability): void
    {
        $item->loadMissing(['staff', 'shift']);
        $date = $item->schedule_date->toDateString();
        if (!$this->isAvailable($item->staff, $date, $item->shift, $availability)) {
            throw ValidationException::withMessages(['assignments' => "{$item->staff->name} tidak tersedia pada {$date}."]);
        }
        if ($this->hasConflict($item->staff_id, $date, $item->shift, $assignments, $item->id)) {
            throw ValidationException::withMessages(['assignments' => "{$item->staff->name} memiliki jadwal pada jam yang bertabrakan."]);
        }
    }
}
