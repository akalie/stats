<?php

class DaemonsController extends BaseController {

    public function ParsePostChunk() {
        $queue = QueueRepository::getQueue(QueueRepository::QT_POSTS);
        QueueRepository::lockQueue($queue->id);

        QueueRepository::unlockQueue($queue->id);
    }


}
