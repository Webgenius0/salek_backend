<?php

namespace App\Rules;

use Closure;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTimeRange implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = str_replace(' ', '', $value);

        $pattern = '/^\d{1,2}\.\d{2}(AM|PM)-\d{1,2}\.\d{2}(AM|PM)$/';

        if (!preg_match($pattern, $value)) {
            $fail('The :attribute must be in the format "hh.mmAM - hh.mmPM" without spaces.');
            return;
        }

        $timeRange = explode('-', $value);

        try {
            $startTime = Carbon::createFromFormat('h.iA', $timeRange[0]);
            $endTime   = Carbon::createFromFormat('h.iA', $timeRange[1]);

            if ($startTime->greaterThanOrEqualTo($endTime)) {
                $fail('The start time must be earlier than the end time.');
            }
        } catch (\Exception $e) {
            $fail('The :attribute must be in a valid time format.');
        }
    }
}
