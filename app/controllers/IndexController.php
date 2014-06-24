<?php

class IndexController extends BaseController {

	public function showIndex() {

	}

    /**
     * контрол основной страницы
     */
    public function showForm() {
        // url, shortname, id паблика, который надо добавить на парс
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
                    $errorMsg = 'Уже есть в системе';
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
                $pulicName          = isset($publicInfoById[$queue->public_id]->name) ? $publicInfoById[$queue->public_id]->name : $queue->public_id;
                $queuesInfo[$queue->public_id] = [
                    'title'         =>  $pulicName,
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

    /**
     * контролл страницы токенов
     *
     * @return $this|\Illuminate\View\View
     */
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

    /**
     * контролл парса отдельных постов
     *
     * @return $this|\Illuminate\View\View
     */
    public function postForm() {
        $errorMsg   = null;
        $label      = Input::get('label');
        $postIds    = Input::get('postIds');
        $file       = Input::file('f');

        if (!empty($file) && $file->isValid()) {
            $postIds = file_get_contents($file->getRealPath());
        }

        if (!$label) {
            $label = (new \DateTime())->format('h-i_d-m-Y');
        }

        if ($postIds) {
            $postIds = explode(',', $postIds);
            $postIds = preg_grep('/-?\d+_\d+/', $postIds);

            if (empty($postIds) || count($postIds) > 1000) {
                $errorMsg = '<a href="http://youtu.be/OLmKm7fYk7c?t=4m46s" target="_blank">Неет</a>';
            } else {
                $postRawId = PostRawsRepository::createNewPostRaw($label, implode(',', $postIds));
                if (QueueRepository::createNewExactPostsQueues($label, $postRawId)) {
                    StatRepository::createTablesForExactPosts($label);
                } else {
                    $errorMsg = 'Уже есть в системе';
                }
            }
        }

        $queues = QueueRepository::getAllQueues(QueueRepository::QT_EXACT_POSTS);
        foreach($queues as $queue) {

            $postLikesPath      = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_LIKES);
            $postRepostsPath    = FileHelper::getCsvPath($queue->public_id, StatRepository::POST_REPOSTS);
            // todo
//            $allPath            = FileHelper::getCsvPath($queue->public_id, StatRepository::ALL);

            $queuesInfo[$queue->public_id] = [
                'label'         =>  $queue->public_id,
                'postLikes'     =>  is_file($postLikesPath) ? basename($postLikesPath) : null,
                'postReposts'   =>  is_file($postRepostsPath) ? basename($postRepostsPath) : null,
//                'boardRepls'    =>  is_file($allPath) ? basename($allPath) : null,
                'queueId'       =>  $queue->id
            ];

        }
        return View::make('statPost')
            ->with('queuesInfo', $queuesInfo)
            ->with('errorMsg', $errorMsg);
    }

    /**
     * возвращает id паблика по введенному url, shortname, id ...
     *
     * @param $stringId - url паблика, его id или shortlink
     *
     * @return bool | int
     */
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
}
