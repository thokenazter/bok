<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Mapping bulan Indonesia ke bahasa Inggris
     */
    private static $monthMap = [
        'Januari' => 'January',
        'Februari' => 'February', 
        'Maret' => 'March',
        'April' => 'April',
        'Mei' => 'May',
        'Juni' => 'June',
        'Juli' => 'July',
        'Agustus' => 'August',
        'September' => 'September',
        'Oktober' => 'October',
        'November' => 'November',
        'Desember' => 'December'
    ];

    /**
     * Parse tanggal kegiatan manual ke format Carbon
     * 
     * @param string $dateString Format: "27 s/d 29 Agustus 2025" atau "26 Januari 2025"
     * @return Carbon|null
     */
    public static function parseActivityDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Bersihkan string dari karakter yang tidak perlu
            $dateString = trim($dateString);
            
            // Pattern 1: "DD s/d DD Bulan YYYY" atau "DD dan DD Bulan YYYY"
            if (preg_match('/(\d{1,2})\s+(s\/d|dan)\s+(\d{1,2})\s+(\w+)\s+(\d{4})/', $dateString, $matches)) {
                $startDay = $matches[1];
                $endDay = $matches[3];
                $month = $matches[4];
                $year = $matches[5];
                
                // Gunakan tanggal mulai untuk trend
                $monthEng = self::$monthMap[$month] ?? $month;
                return Carbon::createFromFormat('j F Y', "$startDay $monthEng $year");
            }
            
            // Pattern 2: "DD Bulan YYYY"
            if (preg_match('/(\d{1,2})\s+(\w+)\s+(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                $monthEng = self::$monthMap[$month] ?? $month;
                return Carbon::createFromFormat('j F Y', "$day $monthEng $year");
            }
            
            // Pattern 3: Format DD-MM-YYYY (jika ada)
            if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                return Carbon::createFromFormat('d-m-Y', "$day-$month-$year");
            }
            
            return null;
            
        } catch (\Exception $e) {
            // Log error tapi jangan crash
            \Log::warning("Failed to parse activity date: $dateString", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse tanggal surat manual ke format Carbon
     * 
     * @param string $dateString Format: "27 Agustus 2025"
     * @return Carbon|null
     */
    public static function parseDocumentDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Bersihkan string
            $dateString = trim($dateString);
            
            // Pattern: "DD Bulan YYYY"
            if (preg_match('/(\d{1,2})\s+(\w+)\s+(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                $monthEng = self::$monthMap[$month] ?? $month;
                return Carbon::createFromFormat('j F Y', "$day $monthEng $year");
            }
            
            // Pattern: DD-MM-YYYY
            if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                return Carbon::createFromFormat('d-m-Y', "$day-$month-$year");
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::warning("Failed to parse document date: $dateString", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get month and year from activity date for trending
     * 
     * @param string $dateString
     * @return array ['month' => int, 'year' => int] or null
     */
    public static function getMonthYearFromActivity($dateString)
    {
        $date = self::parseActivityDate($dateString);
        
        if ($date) {
            return [
                'month' => $date->month,
                'year' => $date->year
            ];
        }
        
        return null;
    }

    /**
     * Get month and year from document date for trending fallback
     * 
     * @param string $dateString
     * @return array ['month' => int, 'year' => int] or null
     */
    public static function getMonthYearFromDocument($dateString)
    {
        $date = self::parseDocumentDate($dateString);
        
        if ($date) {
            return [
                'month' => $date->month,
                'year' => $date->year
            ];
        }
        
        return null;
    }

    /**
     * Format tanggal ke bahasa Indonesia
     * 
     * @param Carbon $date
     * @param string $format Format yang diinginkan (default: 'd F Y')
     * @return string
     */
    public static function formatIndonesian(Carbon $date, $format = 'd F Y')
    {
        // Mapping bulan Inggris ke Indonesia (kebalikan dari $monthMap)
        $indonesianMonths = [
            'January' => 'Januari',
            'February' => 'Februari', 
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        // Format tanggal dengan format bahasa Inggris terlebih dahulu
        $formattedDate = $date->format($format);
        
        // Ganti nama bulan Inggris dengan Indonesia
        foreach ($indonesianMonths as $english => $indonesian) {
            $formattedDate = str_replace($english, $indonesian, $formattedDate);
        }
        
        return $formattedDate;
    }
}
