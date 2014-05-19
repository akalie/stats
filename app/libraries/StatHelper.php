<?php
    class   StatHelper {
        public static function getBorderCommentersIds($boardId) {
            $params = [];
            $result['users'] = [];

            list($params['group_id'], $params['topic_id']) = explode('_', $boardId);
            $params['count'] = 100;
            $params['access_token'] = '9bb9211dae396aac1137e3220a6d52e339e2e9ae3fd68a9a6d114b866758e04241cd0ebe431d66eb2ceec';
            $params['v'] = '5.21';
            $offset = 0;
            $i = 0;
            while($i++ < 225) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('board.getComments', $params);
                $result['users'] = array_merge($result['users'], array_map(function ($item) {return  $item->from_id;}, $response->items));
                if(!count($response->items)) {
                    break;
                }
                $offset += 100;
            }

            return array_unique($result['users']);
        }

        public static function getPostLikersIds($postId) {
            $params = [];
            $result['users'] = [];

            list($params['owner_id'], $params['item_id']) = explode('_', $postId);
            $params['count'] = 1000;
            $params['type'] = 'post';
            $params['filter'] = 'likes';
            $params['friends_only'] = 0;
            $params['v'] = '5.21';
            $offset = 0;
            $i = 0;
            while($i++ < 225) {
                $params['offset'] = $offset;
                $response = VkHelper::api_request('likes.getList', $params);
                $result['users'] = array_merge($result['users'],  $response->items);
                #$result['groups'] = array_map(function ($group) { return $group->id; }, $response->groups);
                if(!count($response->items)) {
                    break;
                }
                $offset += 1000;

            }
            return array_unique($result['users']);

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