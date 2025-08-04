<?php

namespace App\Helpers;

class WilayahHelper
{
    public static function getWilayahList()
    {
        $file = storage_path('app/kode_wilayah.csv');
        $data = array_map('str_getcsv', file($file));
        $wilayah = [];

        foreach ($data as $row) {
            if (count($row) === 2 && strlen($row[0]) === 13) {
                $wilayah[$row[0]] = $row[1];
            }
        }

        return $wilayah;
    }
}
