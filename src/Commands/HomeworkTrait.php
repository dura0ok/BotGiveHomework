<?php


namespace App\Commands;

date_default_timezone_set('Europe/Moscow');

use App\DB;
use DateTime;

trait HomeworkTrait
{
    public static function datetimeUserFriendly($date)
    {
        $dateDateTime = new DateTime($date);
        $date = $dateDateTime->getTimestamp();
        // Вывод даты на русском
        $monthes = array(
            1 => 'Января',
            2 => 'Февраля',
            3 => 'Марта',
            4 => 'Апреля',
            5 => 'Мая',
            6 => 'Июня',
            7 => 'Июля',
            8 => 'Августа',
            9 => 'Сентября',
            10 => 'Октября',
            11 => 'Ноября',
            12 => 'Декабря',
        );
        $stringDateTime = date('d ', $date) . $monthes[(date('n', $date))] . date(' Y, H:i', $date);

        $days = array(
            'Воскресенье',
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
            'Суббота',
        );

        $stringDateTime .= ', ' . $days[(date('w', $date))];
        return $stringDateTime;
    }

    public function saveHomework($body, $attachments = null, $user_id = null)
    {
        $values = [];
        $str = trim(preg_replace('/\s+/', ' ', $body));
        $ex = explode(' ', $str);
        unset($ex[0]);
        $key = array_search("надату", $ex);
        if ($attachments != null and $user_id != null) {
            $values['attachments'] = serialize($attachments);
            $values['user_id'] = $user_id;
            $values['text'] = $body;

            $stmt = DB::prepare("INSERT INTO heap (attachments, user_id, text, todate) VALUES (:attachments, :user_id, :text, :todate)");

        } else {

            $stmt = DB::prepare("INSERT INTO homework (text, todate) VALUES (:text, :todate)");
        }
        $response = $this->recognizeDate($str, "надату");
        $values['todate'] = $response['todate'];
        $values['text'] = $this->mb_ucfirst($response['text']);
        $stmt->execute($values);

    }

    public function recognizeDate(string $str, string $needle): array
    {
        $response = [];
        $str = trim(preg_replace('/\s+/', ' ', $str));
        $ex = explode(' ', $str);
        unset($ex[0]);
        $key = array_search($needle, $ex);
        if ($key !== false) {
            unset($ex[$key]);
            if (strpos($ex[$key + 1], '.') !== false) {
                $md = explode('.', $ex[$key + 1]);
                $day = $md[0];
                $month = $md[1];
                $dateTime = new DateTime(date("Y-{$month}-{$day} H:i:s"));
                $response['todate'] = $dateTime->format('Y-m-d H:i:s');
                unset($ex[$key + 1]);
            } else {

                $day = $ex[$key + 1];
                $dateTime = new DateTime(date("Y-m-{$day} H:i:s"));
                $response['todate'] = $dateTime->format('Y-m-d H:i:s');
                unset($ex[$key + 1]);

            }
        } else {
            $date = (new \DateTime());
            $dayOfWeek = $date->format('w');
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                $todate = date("Y-m-d", strtotime('monday next week'));
            } else {
                $todate = date("Y-m-d", strtotime('tomorrow'));
            }
            $response['todate'] = $todate;

        }
        $response['text'] = implode(' ', $ex);
        return $response;
    }

    public function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

    public function getHomework(bool $history = false): array
    {
        $endpoints = $this->getEndPoints($history);
        $begin = date('Y-m-d H:i:s', $endpoints['start']);
        $end = date('Y-m-d H:i:s', $endpoints['end']);

        $message = " \n _________________ \n";
        $smnt = DB::prepare('SELECT * FROM homework WHERE todate between ? AND ? ORDER BY todate');
        $smnt->execute(array($begin, $end));
        $homeworks = $smnt->fetchAll();
        return $homeworks;
    }

    private function getEndPoints(bool $history = true): array
    {
        $date = (new DateTime());
        $dayOfWeek = $date->format('w');
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            $startWeek = strtotime("monday next week");
            $endWeek = strtotime("sunday next week");
            return ['start' => $startWeek, 'end' => $endWeek];
        } else {
            if ($history == false) {
                $startWeek = strtotime('tomorrow');
            } else {
                $startWeek = strtotime("monday this week");
            }
            $endWeek = strtotime("sunday this week");
            return ['start' => $startWeek, 'end' => $endWeek];
        }
    }


}