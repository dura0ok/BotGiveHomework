<?php

namespace App;

use App\Commands\AddHomeworkCommand;
use App\Commands\AddAdminCommand;
use App\Commands\DefaultCommand;
use App\Commands\DeleteHomeworkCommand;
use App\Commands\GiveCommandsCommand;
use App\Commands\GiveHomeworkCommand;
use App\Commands\HelloCommand;
use App\Commands\HomeworkHistoryCommand;
use App\Commands\MailingCommand;
use App\Commands\StatusCommand;
use App\Commands\SubscribeCommand;
use App\Commands\TimetableCommand;
use App\Commands\YesCommand;
use VK\CallbackApi\Server\VKCallbackApiServerHandler;
use VK\Client\VKApiClient;


class ServerHandler extends VKCallbackApiServerHandler
{
    private $secret;
    private $group_id;
    private $confirmation_token;
    private $token;
    private $vk;
    private $buttons;

    public function __construct($secret, $group_id, $confirmation_token, $token)
    {
        $this->secret = $secret;
        $this->group_id = $group_id;
        $this->confirmation_token = $confirmation_token;
        $this->token = $token;
        $this->vk = new VKAPIClient();
        $this->buttons = file_get_contents(__DIR__ . '/../lib/buttons.json');
    }

    public function parse($data)
    {

        if ($data['type'] == 'confirmation') {
            $this->confirmation($data['group_id'], $data['secret']);
        } else {
            $default = new DefaultCommand();
            $hello = new HelloCommand($this->token, $this->vk, $this->buttons);
            $timetable = new TimetableCommand();
            $status = new StatusCommand();
            $mailing = new MailingCommand();
            $homework = new GiveHomeworkCommand();
            $history = new HomeworkHistoryCommand();
            $addHomework = new AddHomeworkCommand();
            $addAdmin = new AddAdminCommand($this->token, $this->vk);
            $deleteHomework = new DeleteHomeworkCommand();
            $subscribe = new SubscribeCommand($this->token, $this->vk);
            $giveCommands = new GiveCommandsCommand();
            $yes = new YesCommand();
            //Ищем обработчик нашей команды
            $strategyContext = new StrategyContext($default, $hello, $timetable, $status, $mailing, $homework, $history,
                $addHomework, $addAdmin, $subscribe, $giveCommands, $deleteHomework, $yes);
            //Получаем ответ
            $response = $strategyContext->run($data);
            $params = ['peer_id' => $data['object']['peer_id'], 'random_id' => mt_rand(1, 9999)];
            $params = array_merge($response, $params);
            if (isset($params['message']) and $params['message'] != "") {
                $this->vk->messages()->send($this->token, $params);
            }

        }
    }

    public function confirmation(int $group_id, ?string $secret)
    {
        $group_id = strval($group_id);
        if ($secret === $_ENV['SECRET'] && $group_id === $_ENV['GROUP_ID']) {
            echo $_ENV['CONFIRMATION_TOKEN'];
        }
    }

}
