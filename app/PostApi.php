<?php

declare(strict_types=1);

require 'vendor/autoload.php';

require_once('Api.php');
require_once('Database.php');
require_once('Models/Post.php');
require_once('Repositories/PostRepository.php');
require_once('Services/CacheService.php');

use Api\Api;
use Api\Database;
use Api\Models\Post;
use Api\Services\CacheService;


class PostApi extends Api
{
    const CACHE_KEY = 'user_feed_%s';
    public function create($request)
    {
        $decoded = $this->checkAuth();

        $userId = $decoded->id;
        $text = $this->postRequest['text'] ?? null;

        if (empty($text)) {
            return $this->response(['message' => 'Text is required.'], 422);
        }

        $pdo = (new Database())->getConnection();

        $date = new \Datetime();
        $formattedDate = $date->format('Y-m-d G:i:s');
        $stmt = $pdo->prepare("INSERT INTO posts(user_id, text, created_at) VALUES (?, ?, ?) RETURNING id");

        if ($stmt->execute([$userId, $text, $formattedDate])) {
            $postId = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$postId['id']]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            $redis = new \Redis();
            $redis->connect('redis');

            $cacheKey = sprintf(self::CACHE_KEY, $userId);
            $redis->lPush($cacheKey, json_encode($post));

            return $this->response('Post is added successfully', 200);
        } else {
            return $this->response('Failed to add post', 500);
        }
    }

    public function show($request)
    {
        $postId = $request['id'] ?? '';
        $pdo = (new Database())->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $rowPost = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($rowPost)) {
            return $this->response('Post is not found', 404);
        }

        $post = new Post();

        $post->setId($postId);
        $post->setUserId($rowPost['user_id']);
        $post->setText($rowPost['text']);

        return $this->response($post, 200);
    }

    public function update()
    {
        $decoded = $this->checkAuth();

        $userId = $decoded->id;
        $postId = $this->postRequest['id'] ?? null;
        $text = $this->postRequest['text'] ?? null;

        $pdo = (new Database())->getConnection();

        $stmt = $pdo->prepare("UPDATE posts SET text = ? WHERE user_id = ? AND id = ?");

        if ($stmt->execute([$text, $userId, $postId])) {
            //не инвалидируем кэш, через час этот пост обновится
            return $this->response('Post is updated successfully', 200);
        } else {
            return $this->response('Failed to update post', 500);
        }
    }

    public function delete($request)
    {
        $decoded = $this->checkAuth();

        $userId = $decoded->id;
        $postId = $request['id'] ?? '';

        $pdo = (new Database())->getConnection();

        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ? AND id = ?");

        if ($stmt->execute([$userId, $postId])) {
            $cacheService = new CacheService();
            $cacheService->updateUserPostFeed($userId);

            return $this->response('Post is deleted successfully', 200);
        } else {
            return $this->response('Failed to delete post', 500);
        }
    }

    public function getFeed()
    {
        $decoded = $this->checkAuth();

        $userId = $decoded->id;
        $offset = $this->postRequest['offset'] ?? 0;
        $limit = $this->postRequest['limit'] ?? 20;

        $cacheService = new CacheService();
        $feed = $cacheService->getUserPostsFeed($userId, $offset, $limit);

        return $this->response($feed, 200);
    }

}