<?php

namespace App\Rules;

use App\Enums\DifficultyUnit;
use App\Models\Exercise;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HeartRateZoneWithinRange implements ValidationRule
{
    /**
     * When the set's exercise uses the heart-rate-zone difficulty unit, the
     * difficulty value must be an integer between 1 and 5. Null (no target
     * zone, e.g. a drill) and non-zone units are left untouched.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $exerciseKey = preg_replace('/\.sets\.\d+\.difficulty_value$/', '.exercise_id', $attribute);
        $exerciseId = data_get(request()->all(), $exerciseKey);

        if (! $exerciseId) {
            return;
        }

        $exercise = Exercise::with('equipment')->find($exerciseId);

        if ($exercise?->equipment?->difficulty_unit !== DifficultyUnit::HeartRateZone) {
            return;
        }

        if (! is_numeric($value) || (int) $value != $value || $value < 1 || $value > 5) {
            $fail(__('Heart-rate zone must be between 1 and 5.'));
        }
    }
}
