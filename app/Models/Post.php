<?php

declare(strict_types=1);

namespace Api\Models;

class Post {

    public $id;
    public $userId;
    public $text;

    public function setId($id) {
        $this->id = $id;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setText($text) {
        $this->text = $text;
    }
}