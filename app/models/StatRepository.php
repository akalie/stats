<?php

/**
 * @author akalie
 */

class StatRepository  {
    const
        STAT_TABLE_PUBLIC_PREFIX = 'stp_id_', // префикс для названия таблиц с id
        POST_LIKES      = 'post_likes',    // суффикс для таблицы с id лайкнувших посты
        POST_REPOSTS    = 'post_reposts',  // суффикс для таблицы с id репостнувших посты
        ALBUM_LIKES     = 'album_likes',   // суффикс для таблицы с id лайкнувших фото
        ALBUM_REPOSTS   = 'album_reposts', // суффикс для таблицы с id репостнувших фото
        BOARD_REPLS     = 'board_repls';   // суффикс для таблицы с id отписавшихся в борде

    const
        MAX_IDS_IN_CHUNK = 10000; // максимальное количество id, возвращаемых за раз из базы

    /**
     * создает таблицу под новый паблик
     *
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

    /**
     * Сохраняет пачку юзерских id в соотв. таблице
     *
     * @param string $type тип статистики (лайки/репосты фоток/постов или обсуждения)
     * @param int $publicId id собщества
     * @param array $userIds массив id юзеров
     */
    public static function saveUserIds($type, $publicId, array $userIds) {
        $tableName = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId . '_' . $type;
        $params = [];
        foreach ($userIds as $id) {
            $params[] = ['user_id' => $id];
        }

        DB::beginTransaction();
        DB::table($tableName)->insert($params);
        DB::commit();
    }

    /**
     * возвращает страницу id
     *
     * @param $type
     * @param $publicId
     * @param $offset
     * @return array|static[]
     */
    public static function GetAllIds($type, $publicId, $offset) {
        $table = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId . '_' . $type;
        return DB::table($table)->distinct()->offset($offset)->limit(self::MAX_IDS_IN_CHUNK)->get();
    }

    /**
     * удаляет все таблицы этого паблика
     *
     * @param $publicId
     */
    public static function deleteTablesForPublic($publicId) {
        $newTableName = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId;
        Schema::dropIfExists($newTableName . '_post_likes');
        Schema::dropIfExists($newTableName . '_post_reposts');
        Schema::dropIfExists($newTableName . '_board_repls');
        Schema::dropIfExists($newTableName . '_album_likes');
        Schema::dropIfExists($newTableName . '_album_reposts');
    }
} 