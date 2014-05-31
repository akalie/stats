<?php

/**
 * @author akalie
 */

class StatRepository  {
    const STAT_TABLE_PUBLIC_PREFIX = 'stp_id_';

    /** создает таблицу под новый паблик
     * @param int $publicId id паблика/группы
     */
    public static function createTableForPublic($publicId) {
        $newTableName = self::STAT_TABLE_PUBLIC_PREFIX . (int) $publicId;
        if (!Schema::hasTable($newTableName)) {
            Schema::create($newTableName , function($table) {
                $table->integer('user_id');
                $table->integer('post_likes');
                $table->integer('post_reposts');
                $table->integer('album_likes');
                $table->integer('album_reposts');
                $table->integer('board_comments');
            });
        }
    }


} 