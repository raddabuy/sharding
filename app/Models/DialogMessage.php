<?php

namespace Api\Models;

class DialogMessage
{
    public $dialogId;
    public $user_from;
    public $user_to;
    public $text;
    public $createdAt;

    public function setDialogId($dialogId) {
        $this->dialogId = $dialogId;
    }

    public function setUserFrom($userFrom) {
        $this->user_from = $userFrom;
    }

    public function setUserTo($userTo) {
        $this->user_to = $userTo;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
}