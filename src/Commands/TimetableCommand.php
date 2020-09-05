<?php


namespace App\Commands;

class TimetableCommand implements Command
{
    public function execute(array $object): array
    {
        $timetable = file_get_contents(__DIR__ . '/../../lib/timetable.txt');
        $message = "Вот ваше расписание! \n -------------------- \n" . $timetable;
        return ['message' => $message];
    }

    public function accept($message): bool
    {
        if ($message == "!расписание") {
            return true;
        }
        return false;
    }
}
