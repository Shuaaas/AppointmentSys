<?php

namespace App\Traits;

trait ConvertsNumbersToWords
{
    private function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'ZERO';
        }

        $units = [
            0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR',
            5 => 'FIVE', 6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE',
            10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN',
            14 => 'FOURTEEN', 15 => 'FIFTEEN', 16 => 'SIXTEEN',
            17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN',
        ];

        $tens = [
            2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
            6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY',
        ];

        $scales = [
            1000000000 => 'BILLION',
            1000000 => 'MILLION',
            1000 => 'THOUSAND',
            100 => 'HUNDRED',
        ];

        foreach ($scales as $scale => $label) {
            if ($number >= $scale) {
                $whole = intdiv($number, $scale);
                $remainder = $number % $scale;
                $words = $this->numberToWords($whole) . ' ' . $label;

                return $remainder > 0
                    ? $words . ' ' . $this->numberToWords($remainder)
                    : $words;
            }
        }

        if ($number < 20) {
            return $units[$number];
        }

        $whole = intdiv($number, 10);
        $remainder = $number % 10;

        return trim($tens[$whole] . ' ' . $units[$remainder]);
    }
}
