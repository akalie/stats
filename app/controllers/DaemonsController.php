<?php

class DaemonsController extends BaseController {

    public function ParsePostChunk() {
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
            print_r($e->getMessage());
            die('не прокатило');
        }
        if ($wallPosts['isLast'] ) {
            //finished
            QueueRepository::updateQueueStatus($queue->id, 2);
        } else {
            QueueRepository::updateProcessed($queue->id, ++$page . '_' . $currentPostId);
        }
        QueueRepository::unlockQueue($queue->id);
    }


}
