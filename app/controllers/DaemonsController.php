<?php

class DaemonsController extends BaseController {

    /**
     * контроллер парсит просты на лайки и репосты
     */
    public function ParsePostChunk() {
        set_time_limit(240);

        $queue = QueueRepository::getQueue(QueueRepository::QT_POSTS);
        if (!$queue) {
            die('nothing to process');
        }
        QueueRepository::lockQueue($queue->id);
        if (!empty($queue->last_processed_id)) {
            list($page, $stopId)  = explode('_', $queue->last_processed_id);
        } else {
            $stopId = null;
            $page = 1;
        }

        try {
            $wallPosts = VkHelper::getWallPage('-' . $queue->public_id, $page);
            $currentPostId = null;
            foreach ( $wallPosts['posts'] as $post) {
                if ($post->id <= $stopId) {
                    continue;
                }
                if (!$currentPostId) {
                    $currentPostId = $post->id;
                }
                $likersIds = StatHelper::getPostLikersIds('-' . $queue->public_id . '_' . $post->id);
                if (count($likersIds)) {
                    StatRepository::saveUserIds(StatRepository::POST_LIKES, $queue->public_id, $likersIds);
                }
                $reposterIds = StatHelper::getPostRepostersIds('-' . $queue->public_id . '_' . $post->id);

                if (count($reposterIds)) {
                    StatRepository::saveUserIds(StatRepository::POST_REPOSTS, $queue->public_id, $reposterIds);
                }
            }
        } catch(Exception $e) {
            //todo логирование
            QueueRepository::unlockQueue($queue->id);
            Log::error($e->getMessage() . 'on ' . __FUNCTION__ . var_export($queue, 1));
            print_r($e->getMessage());
            die('не прокатило');
        }
        if ($wallPosts['isLast'] ) {
            //finished
            QueueRepository::updateQueueStatus($queue->id, 2);
            $allIds =  StatRepository::GetAllIds(StatRepository::POST_LIKES, $queue->public_id);
            $filename = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES );
            FileHelper::array2csv($allIds, $filename);

            unset($allIds);

            $allIds =  StatRepository::GetAllIds(StatRepository::POST_REPOSTS, $queue->public_id);
            $filename = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS );
            FileHelper::array2csv($allIds, $filename);
            unset($allIds);
        } else {
            QueueRepository::updateProcessed($queue->id, ++$page . '_' . $currentPostId);
        }
        QueueRepository::unlockQueue($queue->id);
    }

    /**
     * контроллер парсит обсуждения
     */
    public function ParserBoardsChunk() {
        set_time_limit(240);

        $queue = QueueRepository::getQueue(QueueRepository::QT_BOARDS);
        if (!$queue) {
            die('nothing to process');
        }
        QueueRepository::lockQueue($queue->id);

        $lastBoardId = $queue->last_processed_id ? : 100000000;

        try {
            $boards = VkHelper::getBoards($queue->public_id, $lastBoardId);
            if (!isset($boards->items)) {
                Log::error( 'apparently no boards on ' . __FUNCTION__ . ' ' . var_export($queue, 1));
                QueueRepository::updateQueueStatus($queue->id, 2);
                QueueRepository::unlockQueue($queue->id);
                die();
            }
            foreach ( $boards->items as $board) {
                if ($board->id >= $lastBoardId) {
                    continue;
                }
                $boardIds = StatHelper::getBoardCommentersIds($queue->public_id . '_' . $board->id);
                if (count($boardIds)) {
                    StatRepository::saveUserIds(StatRepository::BOARD_REPLS, $queue->public_id, $boardIds);
                }

                QueueRepository::updateProcessed($queue->id,  $board->id);
            }
        } catch(Exception $e) {
            //todo логирование
            QueueRepository::unlockQueue($queue->id);
            Log::error($e->getMessage() . ' on ' . __FUNCTION__ . var_export($queue, 1));
            die('не прокатило');
        }
        if (count($boards->items) < 100) {
            QueueRepository::updateQueueStatus($queue->id, 2);
            $allIds =  StatRepository::GetAllIds(StatRepository::BOARD_REPLS, $queue->public_id);
            $filename = FileHelper::getCsvPath($queue->public_id, StatRepository::BOARD_REPLS);
            FileHelper::array2csv($allIds, $filename);
            unset($allIds);
        }
        QueueRepository::unlockQueue($queue->id);
    }

    /**
     * контроллер проверяет csv
     */
    public function CheckCSV() {

        $queues = QueueRepository::getFinishedQueues();
        foreach($queues as $queue) {
            echo $queue->id . '<br>';
            if ($queue->type == 2) {
                $csv  = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES);
                $csv2 = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS);
                if (!file_exists($csv)) {
                    echo 'creating csv ' . $csv . PHP_EOL;
                    if (empty($allIds)) {
                        FileHelper::emptyCSV($csv);
                        continue;
                    }
                    $allIds =  StatRepository::GetAllIds(StatRepository::POST_LIKES, $queue->public_id);
                    FileHelper::array2csv($allIds, $csv);
                    die();
                }

                if (!file_exists($csv2)) {
                    echo 'creating csv ' . $csv2 . PHP_EOL;
                    if (empty($allIds)) {
                        FileHelper::emptyCSV($csv2);
                        continue;
                    }
                    $allIds =  StatRepository::GetAllIds(StatRepository::POST_REPOSTS, $queue->public_id);
                    FileHelper::array2csv($allIds, $csv);
                    die();
                }
            } elseif ($queue->type == 3) {
                continue;
            } elseif ($queue->type == 4) {
                $csv  = FileHelper::getCsvPath($queue->public_id, StatRepository::BOARD_REPLS);
                if (!file_exists($csv)) {
                    echo 'creating csv ' . $csv2 . PHP_EOL;
                    $allIds =  StatRepository::GetAllIds(StatRepository::BOARD_REPLS, $queue->public_id);
                    if (empty($allIds)) {
                        FileHelper::emptyCSV($csv);
                        continue;
                    }
                    FileHelper::array2csv($allIds, $csv);
                    die();
                }
            }

        }
    }
}
