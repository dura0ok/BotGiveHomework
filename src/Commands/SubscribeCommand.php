<?php


namespace App\Commands;


use App\DB;

class SubscribeCommand implements Command
{
    private $vk;
    private $token;

    public function __construct($token, $vk)
    {
        $this->token = $token;
        $this->vk = $vk;
    }

    public function execute(array $object): array
    {
        $user_id = $object['peer_id'];
        $row = DB::run("SELECT COUNT(*) AS count FROM recipients WHERE user_id=?", [$user_id])->fetch();
        if ($row['count'] > 0) {
            DB::run("DELETE FROM recipients where user_id = ?", [$user_id]);
            $message = "Вы успешно отписались от рассылки!";
        } else {
            $name = $this->vk->users()->get($this->token, ['user_ids' => $user_id])[0]['first_name'];
            $values = ['name' => $name, 'user_id' => $object['from_id']];
            $stmt = DB::prepare("INSERT INTO recipients (name, user_id) VALUES (:name, :user_id)");
            $stmt->execute($values);
            $message = "Вы успешно подписались на рассылку";
        }
        return ["message" => $message];
    }

    public function accept($message): bool
    {
        if ($message == "!рассылка") {
            return true;
        }
        return false;
    }
}