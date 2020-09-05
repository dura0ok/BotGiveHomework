<?php


namespace App\Commands;


class HelloCommand implements Command
{
    private $vk;
    private $token;
    private $buttons;

    public function __construct($token, $vk, $buttons)
    {
        $this->token = $token;
        $this->vk = $vk;
        $this->buttons = $buttons;
    }

    public function execute(array $object): array
    {
        $name = $this->vk->users()->get($this->token, ['user_ids' => $object['from_id']])[0]['first_name'];
        $message = "Привет, " . $name . "!";
        return ['message' => $message, 'keyboard' => $this->buttons];
    }

    public function accept($message): bool
    {
        if ($message == "привет") {
            return true;
        }
        return false;
    }
}
