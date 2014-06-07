<?php

class DaemonsController extends BaseController {

    /**
     * контроллер парсит посты на лайки и репосты
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
            $checkCount =  0;
            foreach ( $wallPosts['posts'] as $post) {
                if ($post->id <= $stopId) {
                    continue;
                }
                $checkCount++;
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
            QueueRepository::unlockQueue($queue->id);
            Log::error($e->getMessage() . 'on ' . __FUNCTION__ . var_export($queue, 1));
            print_r($e->getMessage());
            die('не прокатило');
        }
        echo PHP_EOL . ' всего постов обработано ' , $checkCount . PHP_EOL;
        if ($wallPosts['isLast'] ) {
            //finished
            QueueRepository::updateQueueStatus($queue->id, 2);
            FileHelper::saveToCSV($queue->public_id, StatRepository::POST_LIKES);
            FileHelper::saveToCSV($queue->public_id, StatRepository::POST_REPOSTS);
        } else {
            QueueRepository::updateProcessed($queue->id, ++$page . '_' . $currentPostId);
        }
        QueueRepository::unlockQueue($queue->id);
    }

    /**
     * контроллер парсит обсуждения
     */
    public function ParseBoardChunk() {
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
            FileHelper::saveToCSV($queue->public_id, StatRepository::BOARD_REPLS);
            unset($allIds);
        }
        QueueRepository::unlockQueue($queue->id);
    }

    /**
     * контроллер парсит альбомы
     */
    public function ParseAlbumChunk() {
        set_time_limit(340);
        $queue = QueueRepository::getQueue(QueueRepository::QT_ALBUMS);
        if (!$queue) {
            die('nothing to process');
        }
        QueueRepository::lockQueue($queue->id);

        try {
            $photoChunk = VkHelper::getPhotoChunk($queue->public_id, $queue->last_processed_id);
            // облом с поиском фотки в альбоме
            if (!is_object($photoChunk) && $photoChunk['error'] == 1) {
                Log::alert('Не нашел фотки ' . var_export($queue, 1));
                // пропускаем альбом
                QueueRepository::updateProcessed($queue->id,  $photoChunk['current_album'] . '_' . 0 . '_' . 1);
                QueueRepository::unlockQueue($queue->id);
                die();
            }

            // считаем, что фотки закончились/их нету
            if (!$photoChunk) {
                echo 'что фотки закончились/их нету ' . var_export($queue, 1). PHP_EOL;
                QueueRepository::updateQueueStatus($queue->id, 2);
                QueueRepository::unlockQueue($queue->id);
                FileHelper::saveToCSV($queue->public_id, StatRepository::ALBUM_LIKES);
                FileHelper::saveToCSV($queue->public_id, StatRepository::ALBUM_REPOSTS);
                die();
            }
            $albumId = $page = $finished = 0;
            if ($queue->last_processed_id) {
                list($albumId, $page, $finished) = explode('_', $queue->last_processed_id);
            }
            foreach ($photoChunk->items as $photo) {
                // получим и сохраним лайки
                $photoLikers = StatHelper::getPhotoIds('-' . $queue->public_id . '_' . $photo->id, StatRepository::ALBUM_LIKES);
                if (count($photoLikers)) {
                    StatRepository::saveUserIds(StatRepository::ALBUM_LIKES, $queue->public_id, $photoLikers);
                }

                // получим и сохраним репосты
                $photoReposters = StatHelper::getPhotoIds('-' . $queue->public_id . '_' . $photo->id, StatRepository::ALBUM_REPOSTS);
                if (count($photoReposters)) {
                    StatRepository::saveUserIds(StatRepository::ALBUM_REPOSTS, $queue->public_id, $photoReposters);
                }

                $albumId = $photo->album_id;
            }

        } catch(Exception $e) {
            //todo логирование
            QueueRepository::unlockQueue($queue->id);
            Log::error($e->getMessage() . ' on ' . __FUNCTION__ . var_export($queue, 1));
            die('не прокатило');
        }

        // считаем, что альбом кончился
        if (count($photoChunk->items) < 500) {
            echo 'альбом кончился ' . count($photoChunk->items) , '<br>';
            QueueRepository::updateProcessed($queue->id, $albumId . '_' . 0 . '_' . 1);
        } else {
            // подготовим очередь для следующей страницы альбома
            QueueRepository::updateProcessed($queue->id, $albumId . '_' . ++$page . '_' . 0);
        }
        QueueRepository::unlockQueue($queue->id);
    }

    /**
     * контроллер проверяет csv, если нет и очередь завершена - создает
     */
    public function CheckCSV() {
        $queues = QueueRepository::getFinishedQueues();
        foreach($queues as $queue) {
            echo $queue->id . '<br>';
            if ($queue->type == QueueRepository::QT_POSTS) {
                if (!file_exists(FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES))) {
                    FileHelper::saveToCSV($queue->public_id, StatRepository::POST_LIKES);
                    die();
                }

                if (!file_exists(FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS))) {
                    FileHelper::saveToCSV($queue->public_id, StatRepository::POST_REPOSTS);
                    die();
                }
            } elseif ($queue->type == QueueRepository::QT_ALBUMS) {
                continue;
            } elseif ($queue->type == QueueRepository::QT_BOARDS) {
                if (!file_exists(FileHelper::getCsvPath($queue->public_id, StatRepository::BOARD_REPLS))) {
                    FileHelper::saveToCSV($queue->public_id, StatRepository::BOARD_REPLS);
                    die();
                }
            }
        }
    }
}
