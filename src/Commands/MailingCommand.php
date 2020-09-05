<?php


namespace App\Commands;


use App\DB;

class MailingCommand implements Command
{
    use AdminTrait;

    public function execute(array $object): array
    {
        $res = $this->isAdmin($object['peer_id']);
        if ($res) {
            $stmt = DB::prepare("INSERT INTO mailings (created_at) VALUES (?)");
            $stmt->execute(array(date('Y-m-d H:i:s')));
            return ['message' => "Рассылка будет произведена в течении 10 минут!Ожидайте"];
        } else {
            return ["message" => "Ошибка прав доступа"];
        }
    }

    public function accept($message): bool
    {
        if ($message == "!разослать") {
            return true;
        }
        return false;
    }
}