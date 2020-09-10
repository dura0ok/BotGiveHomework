<?php


namespace App\Commands;


class GiveCommandsCommand implements Command
{
    public function execute(array $object): array
    {
        return ['message' => "Команды: \n !рассылка - подписаться или отписаться от рассылки \n !статус - проверить подписаны вы на рассылку или нет \n !дз - вручную посмотреть домашнее задание \n !историядз - посмотреть всё дз за неделю \n !добавитьдз самодз надату здесь номер дня на какое задали или номер.месяц \n !добавитьдз Физика §4,§9,§10 надату 18 Вот пример \n Или так !добавитьдз Физика §4,§9,§10 надату 18.09"];
    }

    public function accept($message): bool
    {
        if ($message == "!команды") {
            return true;
        }
        return false;
    }
}