<?php


namespace App\Commands;
use App\DB;

class AddAdminCommand implements Command
{
    use AdminTrait;

    private $vk;
    private $token;

    public function __construct($token, $vk)
    {
        $this->token = $token;
        $this->vk = $vk;
    }

    public function execute(array $object): array
    {
        $res = $this->isAdmin($object['peer_id']);
        $userID = trim(str_replace("!добавитьадмина", "", $object['text']));
        $row = DB::run("SELECT COUNT(*) AS count FROM admins WHERE user_id=?", [$userID])->fetch();
        if ($res) {
            
            if($userID == ""){
                return ["message" => "Чет с userID"];
            }
            if($row['count'] > 0){
                return ["message" => "Такой админ уже есть"];
            }
            $name = $this->vk->users()->get($this->token, ['user_ids' => $userID])[0]['first_name'];
            $values = ['name' => $name, 'user_id' => $userID];
            $stmt = DB::prepare("INSERT INTO admins (name, user_id) VALUES (:name, :user_id)");
            $stmt->execute($values);
            return ["message" => "Успешно добавил админа с именем: ".$name];
        } else {
            return ["message" => "Ошибка прав доступа"];
        }
    }

    public function accept($message): bool
    {
        if (strpos($message, '!добавитьадмина') !== false) {
            return true;
        }
        return false;
    }
}
