<?php


namespace App\Commands;


use App\DB;

class StatusCommand implements Command
{
    public function execute(array $object): array
    {
        $user_id = $object['peer_id'];
        $row = DB::run("SELECT COUNT(*) AS count FROM recipients WHERE user_id=?", [$user_id])->fetch();
        if ($row['count'] > 0) {
            $message = "Вы подписаны на рассылку! B-)";
        } else {
            $message = "Вы не подписаны на рассылку :( \n Подпишитесь на рассылку командой: !рассылка";
        }
        return ["message" => $message];
    }

    public function accept($message): bool
    {
        if ($message == "!статус") {
            return true;
        }
        return false;
    }
}