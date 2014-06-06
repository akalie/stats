<?php
    class   StatHelper {
        public static function getBoardCommentersIds($boardId) {
            $params = [];
            $result = [];

            $token = TokenRepository::getToken();
            list($params['group_id'], $params['topic_id']) = explode('_', $boardId);
            $params['count'] = 100;
            $params['access_token'] = $token->token;
            $params['v'] = '5.21';
            $offset = 0;
            $i = 0;
            while($i++ < 225) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('board.getComments', $params);
                sleep(VkHelper::PAUSE * 2);
                $result = array_merge($result, array_map(function ($item) {return  $item->from_id;}, $response->items));
                if(!count($response->items)) {
                    break;
                }
                $offset += 100;
            }

            return array_unique($result);
        }

        public static function getPostLikersIds($postId) {

            $result = [];

            list($params['owner_id'], $params['item_id']) = explode('_', $postId);
            $params['count'] = 1000;
            $params['type'] = 'post';
            $params['filter'] = 'likes';
            $params['friends_only'] = 0;
            $params['v'] = '5.21';
            $offset = 0;
            $i = 0;
            while ($i++ < 225) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('likes.getList', $params);
                $result = array_merge($result,  $response->items);
                #$result['groups'] = array_map(function ($group) { return $group->id; }, $response->groups);
                if (!count($response->items)) {
                    break;
                }
                $offset += 1000;

            }

            return array_unique($result);
        }

        public static function getPostRepostersIds($postId) {
            $params = [];
            $result['users'] = [];

            list($params['owner_id'], $params['post_id']) = explode('_', $postId);
            $params['count'] = 1000;
            $params['v'] = '5.21';
            $offset = 0;
            $i = 0;
            while($i++ < 25) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('wall.getReposts', $params);
                $result['users'] = array_merge($result['users'], array_map(function ($item) { return (strpos($item->from_id, '-') === false) ? $item->from_id: false; }, $response->items));
                if(!count($response->items)) {
                    break;
                }
                $offset += 1000;

            }
            return array_unique($result['users']);

        }

        /**
         * вернет список лайкнувших/репостнувших фото
         *
         * @param int $photoId полное id фото(-xxx_yyy)
         * @param string $type лайки или репосты
         * @return array
         */
        public static function getPhotoIds($photoId, $type) {
            $result = [];
            $params =  [
                'count'         =>  1000,
                'type'          =>  'photo',
                'filter'        =>  ($type == StatRepository::ALBUM_LIKES) ? 'likes' : 'copies',
                'friends_only'  =>  0,
                'v'             =>  '5.21'
            ];
            list($params['owner_id'], $params['item_id']) = explode('_', $photoId);

            $offset = 0;
            $i = 0;
            while ($i++ < 25) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('likes.getList', $params);
                $result = array_merge($result,  $response->items);
                if (count($response->items) < 1000) {
                    break;
                }
                $offset += 1000;

            }

            return array_unique($result);
        }

        public static function getIds($type, $id) {
            switch($type) {
                case StatRepository::POST_REPOSTS:
                    return self::getPostRepostersIds($id);
                case StatRepository::POST_LIKES :
                    return self::getPostLikersIds($id);
                case StatRepository::BOARD_REPLS:
                    return self::getBoardCommentersIds($id);
                case StatRepository::ALBUM_LIKES:
                    return self::getPhotoIds($id, StatRepository::ALBUM_LIKES);
                case StatRepository::ALBUM_REPOSTS:
                    return self::getPhotoIds($id, StatRepository::ALBUM_LIKES);
                default:
                    return false;
            }
        }


    }
