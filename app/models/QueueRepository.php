<?php

/**
 * @author akalie
 */

class QueueRepository {
    const   QT_PUBLIC       = 1,  // общая очередь для паблика
            QT_POSTS        = 2,  // очередь обработки постов  паблика
            QT_ALBUMS       = 3,  // очередь обработки альбомов для паблика
            QT_BOARDS       = 4,  // очередь обработки обсуждений для паблика
            QT_EXACT_POSTS  = 5   // очередь обработки отдельных постов
    ;

    /** создает очереди для нового паблика
     * @param int $publicId id паблика/группы
     * @return bool
     */
    public static function createNewPublicQueues($publicId) {
        if (self::isQueueAlreadyExists($publicId)) {
            return false;
        }
        $now = new DateTime();
        $commonParams = [
            'percent_done'  =>  0,
            'status_id'     =>  1,
            'created_at'    =>  $now->format('r'),
            'public_id'     =>  $publicId
        ];
        $parentQueueId = DB::table('queues')->insertGetId(
            ['type'  =>  self::QT_PUBLIC] + $commonParams
        );

        $commonParams['parent_queue_id'] = $parentQueueId;

        DB::table('queues')->insertGetId(
            ['type'  =>  self::QT_POSTS] + $commonParams
        );

         DB::table('queues')->insertGetId(
            ['type'  =>  self::QT_ALBUMS] + $commonParams
        );

         DB::table('queues')->insertGetId(
            ['type'  =>  self::QT_BOARDS] + $commonParams
        );
        return true;
    }

    /** Создает очереди для нового набора постов
     * @param string $label лейбл для набора постов
     * @param int $postRawId id строки с постами
     * @return bool
     */
    public static function createNewExactPostsQueues($label, $postRawId) {
        if (self::isQueueAlreadyExists($label)) {
            return false;
        }
        $now = new DateTime();
        $commonParams = [
            'percent_done'  =>  0,
            'status_id'     =>  1,
            'created_at'    =>  $now->format('r'),
            'public_id'     =>  $label
        ];
        $parentQueueId = DB::table('queues')->insertGetId(
            ['type'  =>  self::QT_EXACT_POSTS] + $commonParams
        );

        $commonParams['parent_queue_id'] = $parentQueueId;

        DB::table('queues')->insert(
            ['type'  =>  self::QT_POSTS, 'last_processed_id' => $postRawId] + $commonParams
        );

        return true;
    }

    /** есть ли очередь для этого паблика/лейбла
     * @param string $publicId
     * @return bool
     */
    public static function isQueueAlreadyExists($publicId) {
        return (bool)  DB::table('queues')->where('public_id', $publicId)->where('type', self::QT_PUBLIC)->count();
    }

    /** возвращает незанятую очередь данного типа
     * @param int $queueType
     * @return \Illuminate\Database\Query\Builder|static
     */
    public static function getQueue($queueType) {
        $count = DB::table('queues')
                        ->where('type', $queueType)
                        ->where('status_id', 1)
                        ->whereNotNull('locked_at')
                        ->count();
        if ($count >= 2)
            return false;

        $queue = DB::table('queues')
                    ->where('type', $queueType)
                    ->where('status_id', 1)
                    ->where('locked_at', null)
                    ->first();
        return $queue;
    }

    /** лочим очередь
     * @param int $queueId
     * @return boolean
     */
    public static function lockQueue($queueId) {
        $now = new DateTime();
        return (bool) DB::table('queues')
                        ->where('id', $queueId)
                        ->update(['locked_at' => $now->format("Y-m-d H:i:s")]);
    }

    /** разлочим очередь
     * @param int $queueId
     * @return boolean
     */
    public static function unlockQueue($queueId) {
        return (bool) DB::table('queues')
                        ->where('id', $queueId)
                        ->update(['locked_at' => null]);
    }

    /** обновляем последний обработанный id
     * @param int $queueId
     * @param string $processed
     * @return boolean
     */
    public static function updateProcessed($queueId, $processed) {
        return (bool) DB::table('queues')
                        ->where('id', $queueId)
                        ->update(['last_processed_id' => $processed]);
    }

    /** обновляем статус
     * @param int $queueId
     * @param $status
     * @return boolean
     */
    public static function updateQueueStatus($queueId, $status) {
        return (bool) DB::table('queues')
                        ->where('id', $queueId)
                        ->update(['status_id' => $status]);
    }

    /** получить все очереди (по умолчанию - все "родительские" )
     * @param int $type
     * @return array|static[]
     */
    public static function getAllQueues($type = self::QT_PUBLIC) {
        return DB::table('queues')->where('type', $type)->get();
    }

    /** возвращает все готовые очереди
     * @return array|static[]
     */
    public static function getFinishedQueues() {
        return DB::table('queues')->where('status_id', 2)
            ->where('type', '!=', self::QT_PUBLIC)
            ->get();
    }

    /** удаляет все очереди для паблика (родительскую и подчиненные)
     * @param $queueId
     */
    public static function deleteQueue($queueId) {
        $queue = DB::table('queues')->find($queueId);
        if ($queue) {
            DB::table('queues')->where('parent_queue_id', $queueId)->delete();
            DB::table('queues')->delete($queueId);

            StatRepository::deleteTablesForPublic($queue->public_id);
            FileHelper::deleteAllPublicCSV($queue->public_id);
        }
    }
} 