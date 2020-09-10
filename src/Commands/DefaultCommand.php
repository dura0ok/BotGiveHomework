<?php


namespace App\Commands;


use App\DB;

class DefaultCommand implements Command
{
    public function execute(array $object): array
    {

        if ($object['attachments'][0]["type"] == 'audio_message') {
            $mp3 = $object['attachments'][0]["audio_message"]["link_mp3"];
            $values = ['user_id' => $object['peer_id'], 'link' => $mp3];
            $stmt = DB::prepare("INSERT INTO voiceheap (user_id, link) VALUES (:user_id, :link)");
            $stmt->execute($values);
            return ["message" => "Ожидайте, ваше голосовое сообщение обрабатывается!"];
        }

        return ["message" => ""];
    }

    public function accept($message): bool
    {
        if ($message == "") {
            return true;
        }
        return false;
    }
}