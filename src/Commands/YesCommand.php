<?php


namespace App\Commands;

use App\DB;
use DateTime;

class YesCommand implements Command
{
    public function execute(array $object): array
    {

        $values = [];
        $message = trim(str_replace('!да', '', $object['text']));
        if (strpos($message, 'надату') !== false) {
            $message = explode('надату', $message);
            $id = trim($message[0]);
            $values['id'] = $id;
            $voice = DB::run("SELECT * FROM voiceheap WHERE id=?", [$id])->fetch();
            $todate = trim($message[1]);
            if (strpos($todate, '.') !== false) {
                $todate = explode('.', $todate);
                $day = $todate[0];
                $month = $todate[1];
                $dateTime = new DateTime(date("Y-{$month}-{$day} H:i:s"));
                $values['todate'] = $dateTime;
            } else {
                $day = $todate;
                $dateTime = new DateTime(date("Y-m-{$day} H:i:s"));
                $values['todate'] = $dateTime;
            }
            $values['text'] = $voice['text'];
        } else {
            $id = $message;
            $values['id'] = $id;
            $voice = DB::run("SELECT * FROM voiceheap WHERE id=?", [$id])->fetch();
            if ($voice['todate'] != null) {
                $values['todate'] = new DateTime($voice['todate']);
                $values['text'] = $voice['text'];
            }
        }
        if (isset($values['todate']) && isset($values['text'])) {
            $values['todate'] = $values['todate']->format('Y-m-d H:i:s');
            $id = $values['id'];
            unset($values['id']);
            $stmt = DB::prepare("INSERT INTO homework (text, todate) VALUES (:text, :todate)");
            $stmt->execute($values);
            DB::run("DELETE FROM voiceheap where id = ?", [$id]);
            return ['message' => "Ваше домашнее задание успешно добавлено!"];
        } else {
            return ['message' => "Ошибка"];
        }
    }

    public function accept($message): bool
    {
        if (strpos($message, '!да') !== false) {
            return true;
        }
        return false;
    }
}