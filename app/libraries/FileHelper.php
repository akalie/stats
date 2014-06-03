<?php
/**
 * Created by PhpStorm.
 * User: akalie
 * Date: 6/2/14
 * Time: 9:38 PM
 */

class FileHelper {
    /**
     * @param array $array
     * @param $filename
     * @return bool
     */
    public static function array2csv(array $array, $filename) {
        if (count($array) == 0) {
            return null;
        }
        $df = fopen($filename, 'w');
        fputcsv($df, ['vkId']);
        foreach ($array as $row) {
            fputcsv($df, [$row->user_id]);
        }
        fclose($df);
        return true;
    }

    public static function getCsvPath($publicId, $type, $fullPath = true ) {
        $preable = $fullPath ? public_path() . '/csv/' : '' ;
        return  $preable . $publicId . '_' . $type. '.csv';
    }

    public static function emptyCSV ($filename) {
        $df = fopen($filename, 'w');
        fputcsv($df, ['этих id у паблика нету']);
        fclose($df);
        return true;
    }
} 