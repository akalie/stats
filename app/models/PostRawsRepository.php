<?php

/**
 * @author akalie
 */

class PostRawsRepository {
    const
        POSTS_CHUNK_SIZE = 15;

    /**
     * @param string $label
     * @param array $postIdsRaw
     *
     * @return int
     */
    public static function createNewPostRaw($label, $postIdsRaw) {
        return DB::table('post_ids')->insertGetId([
            'label' =>  $label,
            'posts' =>  $postIdsRaw
        ]);
    }

    /**
     * @param int $id
     *
     * @return array|bool
     */
    public static function popPostId($id) {
        $postIds = DB::table('post_ids')->find($id);
        if (empty($postIds) || !$postIds->posts) {
            // считаем, что очередь закончилась
            return false;
        }

        $posts = explode(',', $postIds->posts);
        $postsSlice = array_slice($posts, 0, self::POSTS_CHUNK_SIZE);
        array_splice($posts, 0, self::POSTS_CHUNK_SIZE);
        $posts = count($posts) ? implode(',', $posts) : '';

        DB::table('post_ids')->where('id', $id)->update(['posts' => $posts]);

        return $postsSlice;
    }

    /**
     * @param int $id
     * @param array $postIdsToPush -xxx_yyy
     *
     * @return bool
     * @throws Exception
     */
    public static function pushPostId($id, array $postIdsToPush) {
        $postIds = DB::table('post_ids')->find($id);
        if (empty($postIds)) {
            throw new Exception('cant find post raw with id ' . $id);
        }

        $posts = (empty($postIds->posts) ? '' : $postIds->posts . ',') . implode(',', $postIdsToPush);

        DB::table('post_ids')->where('id', $id)->update(['posts' => $posts]);
        return true;
    }
} 