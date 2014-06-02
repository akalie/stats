<?php
    class   StatHelper {
        public static function getBoardCommentersIds($boardId) {
            $params = [];
            $result = [];

            list($params['group_id'], $params['topic_id']) = explode('_', $boardId);
            $params['count'] = 100;
            $params['access_token'] = '788acb49f87cbbecafe19a97ead0be698c15fa787bee067bef3df26e0059d86a5f4fe5609826cbe00089b';
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
            $params = [];
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

        public static function getIds($type, $id) {
            switch($type) {
                case 'repost':
                    return self::getPostRepostersIds($id);
                case 'likes' :
                    return self::getPostLikersIds($id);
                case 'borderComments':
                    return self::getBorderCommentersIds($id);
                default:
                    return false;
            }
        }
    }
?>