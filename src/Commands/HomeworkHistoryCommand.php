<?php


namespace App\Commands;


class HomeworkHistoryCommand implements Command
{
    use HomeworkTrait;

    public function execute(array $object): array
    {
        $message = "Вот ваше домашнее задание! \n _________________ \n";
        $homeworks = $this->getHomework(true);
        foreach ($homeworks as $homework) {
            $date = $homework['todate'];
            $userFriendlyDate = self::datetimeUserFriendly($date);
            $message = $message . $homework['text'] . " | Дата - " . $userFriendlyDate . "\n_____________________\n";

        }
        return ['message' => $message];

    }

    public function accept($message): bool
    {
        if ($message == "!историядз") {
            return true;
        }
        return false;
    }
}