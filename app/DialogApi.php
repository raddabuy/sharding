<?php

declare(strict_types=1);

require 'vendor/autoload.php';

require_once('Api.php');
require_once('Database.php');
require_once('Models/DialogMessage.php');

use Api\Api;
use Api\Database;
use Api\Models\DialogMessage;

class DialogApi extends Api
{
    public function sendMessage($request)
    {
        $decoded = $this->checkAuth();

        $userFromId = (int)$decoded->id;
        $userToId = (int)$request['id'];
        $text = $this->postRequest['text'];

        $dialogId = $userFromId > $userToId ? sprintf('%s_%s', $userToId, $userFromId)  : sprintf('%s_%s', $userFromId, $userToId);

        $date = new \Datetime();
        $formattedDate = $date->format('Y-m-d G:i:s');

        if (empty($text)) {
            return $this->response(['message' => 'Text message is required'], 422);
        }

        $pdo = (new Database())->getConnection(true);

        $stmt = $pdo->prepare("INSERT INTO dialog_messages(dialog_id, user_from_id, user_to_id, text, created_at) VALUES (?, ?, ?, ?, ?)");

        if ($stmt->execute([$dialogId, $userFromId, $userToId, $text, $formattedDate])) {
            return $this->response('Message was send successfully', 200);
        } else {
            return $this->response('Failed to send message', 500);
        }
    }

    public function getDialogList($request)
    {
        $decoded = $this->checkAuth();

        $userFromId = $decoded->id;
        $userToId = $request['id'] ?? '';

        $offset = $request['offset'] ?? 0;
        $limit = $request['limit'] ?? 20;

        $pdo = (new Database())->getConnection(true);

        $query = sprintf("SELECT user_from_id, user_to_id, text, created_at FROM dialog_messages WHERE user_from_id = ? AND user_to_id = ? ORDER BY created_at DESC OFFSET %d LIMIT %d", $offset, $limit);
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userFromId, $userToId]);
        $rowMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dialog = [];

        foreach ($rowMessages as $row) {
            $dialogMessage = new DialogMessage();

            $dialogMessage->setUserFrom($row['user_from_id']);
            $dialogMessage->setUserTo($row['user_to_id']);
            $dialogMessage->setText($row['text']);
            $dialogMessage->setCreatedAt($row['created_at']);

            $dialog[] = $dialogMessage;
        }

        return $this->response($dialog, 200);
    }
}