<?php

class IndexController extends BaseController {

	public function showIndex()
	{

	}

    public function showForm()
	{

        $idString = Input::get('idString');
        $errorMsg = null;
        $resultIds = null;
        if ($idString) {
            $id = $this->parsePublicId($idString);
            if (!$id) {
                $errorMsg = 'Не получилось распознать URL поста/топика.';
            }
            if (QueueRepository::createNewPublicQueues($id)) {
                StatRepository::createTablesForPublic($id);
            } else {
                $errorMsg = 'Уже есть в сиситеме';
            }

        }
        $queues = QueueRepository::getAllQueues();
        $queuesInfo = [];
        if (!empty($queues)) {
            foreach( $queues as $queue) {
                $externalIds[] = $queue->public_id;
            }
            $publicInfo = VkHelper::api_request('groups.getById', ['group_ids' => implode(',', $externalIds), 'v '=> 5.21]);

            $publicInfoById = [];
            foreach($publicInfo as $public) {
                $publicInfoById[$public->gid] = $public;
            }
            unset($publicInfo);


            foreach( $queues as $queue) {
                $postLikesPath = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES);
                if (is_file($postLikesPath)) {
                    $postLikesPath = basename($postLikesPath) ;
                } else {
                    $postLikesPath = null;
                }

                $postRepostsPath    = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS);
                if (is_file($postRepostsPath)) {
                    $postRepostsPath = basename($postRepostsPath) ;
                } else {
                    $postRepostsPath = null;
                }

                $boardReplsPath     = FileHelper::getCsvPath($queue->public_id, StatRepository::BOARD_REPLS);
                if (is_file($boardReplsPath)) {
                    $boardReplsPath = basename($boardReplsPath) ;
                } else {
                    $boardReplsPath = null;
                }

                $albumLikesPath     = FileHelper::getCsvPath($queue->public_id, StatRepository::ALBUM_LIKES);
                if (is_file($albumLikesPath)) {
                    $albumLikesPath = basename($albumLikesPath) ;
                } else {
                    $albumLikesPath = null;
                }

                $albumRepostsPath   = FileHelper::getCsvPath($queue->public_id, StatRepository::ALBUM_REPOSTS);
                if (is_file($albumRepostsPath)) {
                    $albumRepostsPath = basename($albumRepostsPath) . '.csv';
                } else {
                    $albumRepostsPath = null;
                }

                $queuesInfo[$queue->public_id] = [
                    'title'         =>  $publicInfoById[$queue->public_id]->name,
                    'postLikes'     =>  $postLikesPath,
                    'postReposts'   =>  $postRepostsPath,
                    'boardRepls'    =>  $boardReplsPath,
                    'albumLikes'    =>  $albumLikesPath,
                    'albumReposts'  =>  $albumRepostsPath,
                ];

            }
        }

        return View::make('statIndex')
            ->with('resultIds', $resultIds)
            ->with('errorMsg', $errorMsg)
            ->with('idString', $idString)
            ->with('queuesInfo', $queuesInfo);
	}


    public function parsePublicId($stringId) {
        if (is_numeric($stringId)) {
            return $stringId;
        }
        if (strpos($stringId, '/') !== false) {
            $url = explode('/', $stringId);
            $shortlink = end($url);
        } else {
            $shortlink = $stringId;
        }

        $groupInfo = VkHelper::api_request('groups.getById', ['group_ids' => $shortlink, 'v' => '5.21']);
        if (isset($groupInfo[0]->id))
            return $groupInfo[0]->id;
        return false;

    }

    public function parseIdString($type, $idString) {
        switch($type) {
            case 'repost':
            case 'likes' :
                if (!preg_match('/wall(-?\d+_\d+)/', $idString, $matches)) {
                    return false;
                }

                return $matches[1];
            case 'borderComments':
                if (!preg_match('/topic-(\d+_\d+)/', $idString, $matches)) {
                    return false;
                }
                return $matches[1];
            default:
                return false;
        }
    }



}
