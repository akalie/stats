<?php

/**
 * @author akalie
 */

class StatRepository  {
    const
        STAT_TABLE_PUBLIC_PREFIX = 'stp_id_',
        POST_LIKES      = 'post_likes',
        POST_REPOSTS    = 'post_reposts',
        ALBUM_LIKES     = 'album_likes',
        ALBUM_REPOSTS   = 'album_reposts',
        BOARD_REPLS     = 'board_repls';


    /** создает таблицу под новый паблик
     * @param int $publicId id паблика/группы
     */
    public static function createTablesForPublic($publicId) {
        $newTableName = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId;
        if (!Schema::hasTable($newTableName . '_post_likes')) {
            Schema::create($newTableName . '_post_likes', function($table) {
                $table->integer('user_id')->index();
            });
            Schema::create($newTableName . '_post_reposts', function($table) {
                $table->integer('user_id')->index();
            });
            Schema::create($newTableName . '_board_repls' , function($table) {
                $table->integer('user_id')->index();
            });
            Schema::create($newTableName . '_album_likes', function($table) {
                $table->integer('user_id')->index();
            });
            Schema::create($newTableName . '_album_reposts', function($table) {
                $table->integer('user_id')->index();
            });
        }
    }

    public static function saveUserIds($type, $publicId, $userIds) {
        $tableName = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId . '_' . $type;
        $params = [];
        foreach ($userIds as $id) {
            $params[] = ['user_id' => $id];
        }

        DB::beginTransaction();
        DB::table($tableName)->insert($params);
        DB::commit();
    }

    public static function GetAllIds($type, $publicId) {
        $table = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId . '_' . $type;
        return DB::table($table)->distinct()->get();
    }
} 