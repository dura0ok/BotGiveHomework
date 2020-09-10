<?php


namespace App\Commands;


use App\DB;

class DeleteHomeworkCommand implements Command
{
    use AdminTrait;

    public function execute(array $object): array
    {
        $res = $this->isAdmin($object['peer_id']);
        if ($res) {
            DB::run("delete from homework order by id desc limit 1");
            $message = "Последнее домашнее задание успешно удалено";
        } else {
            $message = "Ошибка прав доступа!";
        }
        return ["message" => $message];
    }

    public function accept($message): bool
    {
        if ($message == "!удалить") {
            return true;
        }
        return false;
    }
}