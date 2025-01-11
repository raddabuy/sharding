<?php

declare(strict_types=1);

namespace Api\Services;

require 'vendor/autoload.php';

require_once('Repositories/PostRepository.php');
require_once('Database.php');

use Api\Repositories\PostRepository;
use Api\Database;

class CacheService
{
    const CACHE_KEY = 'user_feed_%s';

    public function getUserPostsFeed($userId, $offset = 0, $limit = 20)
    {
        $cacheKey = sprintf(self::CACHE_KEY, $userId);

        $redis = new \Redis();
        $redis->connect('redis');
        $posts = $redis->lRange($cacheKey, $offset, $offset + $limit);

        if (empty($posts)) {
            $postRepository = new PostRepository();
            $posts = $postRepository->getUserPostFeed($userId);

            foreach ($posts as $post) {
                $redis->rPush($cacheKey, json_encode($post));
            }

            $redis->expire($cacheKey, 3600);
        }
        return $posts;
    }

    public function updateUserPostFeed($userId)
    {
        $pdo = (new Database())->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM friends WHERE friend_id = ?");
        $stmt->execute([$userId]);
        $userFriends = $stmt->fetch(PDO::FETCH_ASSOC);

        foreach ($userFriends as $userFriend) {
            $userFriendId = $userFriend['id'];
            $cacheKey = sprintf(self::CACHE_KEY, $userFriendId);

            $redis = new \Redis();
            $redis->connect('redis');

            $redis->del($cacheKey);

            $postRepository = new PostRepository();
            $posts = $postRepository->getUserPostFeed($userFriendId);

            foreach ($posts as $post) {
                $redis->rPush($cacheKey, json_encode($post));
            }

            $redis->expire($cacheKey, 3600);
        }
    }
}