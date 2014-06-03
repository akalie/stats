<?php

/**
 * @author akalie
 */

class TokenRepository  {

    /**
     * если указать id вернет конкретный токен, нет - случайный
     * @param null | int $id
     * @return mixed|static
     */
    public static function getToken($id = null) {
        if ($id) {
            return DB::table('tokens')->where('id', (int) $id)->first();
        } else {
            return DB::table('tokens')->where('status_id', 1)->orderBy(DB::raw('RAND()'))->first();
        }
    }

    /**
     * сохранить токен
     * @param int $userId id пользователя, чей токен
     * @param string $token строка токен
     */
    public static function saveToken($userId, $token) {
        $now = new DateTime();
        DB::table('tokens')->insert([
            'user_id'       =>  (int) $userId,
            'status_id'     =>  1,
            'token'         =>  $token,
            'created_at'    =>  $now->format("Y-m-d H:i:s")
        ]);
    }

    /**
     * удалить токен
     * @param $id
     */
    public static function deleteToken($id) {
        DB::table('tokens')->where('id', (int) $id)->update(['status_id' => 2]);
    }

    /**
     * получить все активные токены
     * @return array|static[]
     */
    public static function getAllTokens() {
        return DB::table('tokens')->where('status_id', 1)->get();
    }
}