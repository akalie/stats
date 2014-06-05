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
        $preamble = $fullPath ? public_path() . '/csv/' : '' ;
        return  $preamble . $publicId . '_' . $type. '.csv';
    }

    public static function emptyCSV ($filename) {
        $df = fopen($filename, 'w');
        fputcsv($df, ['этих id у паблика нету']);
        fclose($df);
        return true;
    }

    /**
     * сохраняет все id из таблицы в файл
     *
     * @param int $publicId
     * @param string $type
     */
    public static function saveToCSV($publicId, $type) {
        set_time_limit(350);
        $count = StatRepository::MAX_IDS_IN_CHUNK;
        $offset = 0;

        // создаем временный файл, который будем набивать id
        $tempFilepath = self::getCsvPath($publicId, 'temp' . $type);
        $df = fopen($tempFilepath, 'w');
        fputcsv($df, ['vkId']);

        while ($count == StatRepository::MAX_IDS_IN_CHUNK) {
            $idsChunk = StatRepository::GetAllIds($type, $publicId, $offset);
            foreach ($idsChunk as $row) {
                fputcsv($df, [$row->user_id]);
            }

            if (empty($idsChunk)) {
                fputcsv($df, ['этих id у паблика нету']);
                break;
            }

            $count = count($idsChunk);
            $offset += StatRepository::MAX_IDS_IN_CHUNK;
        }

        fclose($df);
        $filepath = self::getCsvPath($publicId, $type);
        rename ($tempFilepath, $filepath);
    }

    public static function deleteAllPublicCSV($publicId) {
        $types = [
                    StatRepository::POST_LIKES,
                    StatRepository::POST_REPOSTS,
                    StatRepository::ALBUM_LIKES,
                    StatRepository::ALBUM_REPOSTS,
                    StatRepository::BOARD_REPLS
                 ];

        foreach ($types as $type) {
            $path = self::getCsvPath($publicId, $type);
            @unlink($path);
        }
    }
} 