<?php

class IndexController extends BaseController {

	public function showIndex() {
	}

    /**
     * контрол основной страницы
     */
    public function showForm() {
        $idString = Input::get('idString');
        $errorMsg = null;
        $resultIds = null;
        if ($idString) {
            $id = $this->parsePublicId($idString);
            if (!$id) {
                $errorMsg = 'Не получилось распознать URL паблика';
            } else {
                if (QueueRepository::createNewPublicQueues($id)) {
                    StatRepository::createTablesForPublic($id);
                } else {
                    $errorMsg = 'Уже есть в сиситеме';
                }
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

            foreach($queues as $queue) {

                $postLikesPath      = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES);
                $postRepostsPath    = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS);
                $albumLikesPath     = FileHelper::getCsvPath($queue->public_id, StatRepository::ALBUM_LIKES);
                $albumRepostsPath   = FileHelper::getCsvPath($queue->public_id, StatRepository::ALBUM_REPOSTS);
                $boardReplsPath     = FileHelper::getCsvPath($queue->public_id, StatRepository::BOARD_REPLS);

                $queuesInfo[$queue->public_id] = [
                    'title'         =>  $publicInfoById[$queue->public_id]->name,
                    'postLikes'     =>  is_file($postLikesPath) ? basename($postLikesPath) : null,
                    'postReposts'   =>  is_file($postRepostsPath) ? basename($postRepostsPath) : null,
                    'boardRepls'    =>  is_file($boardReplsPath) ? basename($boardReplsPath) : null,
                    'albumLikes'    =>  is_file($albumLikesPath) ? basename($albumLikesPath) : null,
                    'albumReposts'  =>  is_file($albumRepostsPath) ? basename($albumRepostsPath) : null,
                    'publicId'      =>  $queue->public_id,
                    'queueId'       =>  $queue->id
                ];

            }
        }

        return View::make('statIndex')
            ->with('resultIds', $resultIds)
            ->with('errorMsg', $errorMsg)
            ->with('idString', $idString)
            ->with('queuesInfo', $queuesInfo);
	}

    public function tokenForm() {
        $newToken = Input::get('newToken');
        $userId   = Input::get('userId');
        $errorMsg = null;

        if ($newToken && $userId && is_numeric($userId)) {
            TokenRepository::saveToken($userId, $newToken);
            $errorMsg = 'Success!';
        } elseif ($newToken || $userId) {
            $errorMsg = 'Неправильные данные';
        }

        $tokens = TokenRepository::getAllTokens();

        return View::make('statTokens')
            ->with('errorMsg', $errorMsg)
            ->with('tokens', $tokens);
    }

    private function parsePublicId($stringId) {
        if (is_numeric($stringId)) {
            return $stringId;
        }
        if (strpos($stringId, '/') !== false) {
            $url = explode('/', $stringId);
            $shortlink = end($url);
            if (strpos($shortlink, 'public') === 0 or strpos($shortlink, 'club')) {
                $shortlink = str_replace(['public', 'club'], '', $shortlink);
            }
        } else {
            $shortlink = $stringId;
        }
        try {
            $groupInfo = VkHelper::api_request('groups.getById', ['group_ids' => $shortlink, 'v' => '5.21']);
        } catch (Exception $e) {
            return false;
        }
        if (isset($groupInfo[0]) && isset($groupInfo[0]))
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
