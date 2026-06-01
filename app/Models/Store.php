<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'delivery_fee',
        'min_order',
        'free_delivery_from',
        'phone',
        'address',            // <-- новый адрес магазина
        'working_hours',
    ];

    protected $casts = [
        'working_hours' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Проверить, открыт ли магазин прямо сейчас (часовой пояс Озёрска, UTC+5).
     */
    public function isOpenNow(): bool
    {
        $hours = $this->working_hours;
        if (empty($hours)) return false;

        $now = Carbon::now()->setTimezone('Asia/Yekaterinburg'); // UTC+5
        $dayIndex = $now->dayOfWeekIso - 1; // 0 (Пн) – 6 (Вс)
        $today = $hours[$dayIndex] ?? null;

        if (empty($today['open']) || empty($today['close'])) return false;

        // Круглосуточно, если время открытия и закрытия совпадают
        if ($today['open'] === $today['close']) return true;

        $openTime = Carbon::createFromFormat('H:i', $today['open'], 'Asia/Yekaterinburg');
        $closeTime = Carbon::createFromFormat('H:i', $today['close'], 'Asia/Yekaterinburg');

        if ($closeTime->lt($openTime)) {
            $closeTime->addDay();
        }

        return $now->between($openTime, $closeTime);
    }

    /**
     * Компактная строка графика работы.
     */
    public function getWorkingHoursSummaryAttribute(): string
    {
        $hours = $this->working_hours;
        if (empty($hours)) return 'Не указан';

        $dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        $schedule = [];
        foreach ($dayNames as $i => $short) {
            $day = $hours[$i] ?? null;
            if (empty($day['open']) || empty($day['close'])) {
                $schedule[$i] = null; // выходной
            } elseif ($day['open'] === $day['close']) {
                $schedule[$i] = '24/7'; // круглосуточно
            } else {
                $schedule[$i] = $day['open'] . '–' . $day['close'];
            }
        }

        // Проверка, все ли дни одинаковые
        $unique = array_unique($schedule);
        if (count($unique) === 1) {
            $val = reset($unique);
            if ($val === null) return 'Выходной';
            if ($val === '24/7') return 'Круглосуточно';
            return 'Ежедневно ' . $val;
        }

        // Группировка последовательных дней с одинаковым временем
        $groups = [];
        $start = 0;
        for ($i = 1; $i <= 7; $i++) {
            if ($i === 7 || $schedule[$i] !== $schedule[$start]) {
                $end = $i - 1;
                $range = ($start === $end) ? $dayNames[$start] : $dayNames[$start] . '–' . $dayNames[$end];
                $time = $schedule[$start];
                $groups[] = $range . ': ' . ($time ?? 'выходной');
                $start = $i;
            }
        }

        return implode(', ', $groups);
    }
}