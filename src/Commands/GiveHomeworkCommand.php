<?php


namespace App\Commands;


use DateTime;

class GiveHomeworkCommand implements Command
{
    use HomeworkTrait;

    private $re = "/^\d{1,2}\.\d{1,2}\-\d{1,2}\.\d{1,2}$/";
    public function execute(array $object = []): array
    {
    	$endpoints = trim(str_replace('!дз', '', $object['text']));
        $message = "Вот ваше домашнее задание! \n _________________ \n";
        if(preg_match($this->re, $endpoints) == 0){
        	$homeworks = $this->getHomework();
    	}else{
    		$points = explode('-', $endpoints);
    		$start = DateTime::createFromFormat('d.m', $points[0])->getTimestamp();
    		$end = DateTime::createFromFormat('d.m', $points[1])->getTimestamp();
    		$homeworks = $this->getHomework(false, $start, $end);
    	}

        foreach ($homeworks as $homework) {
            $date = $homework['todate'];
            $userFriendlyDate = self::datetimeUserFriendly($date);
            $message = $message . $homework['text'] . " | Дата - " . $userFriendlyDate . "\n_____________________\n";

        }
        return ['message' => $message];

    }

    public function accept($message): bool
    {

    	$endpoints = trim(str_replace('!дз', '', $message));
        if ($message == '!дз' || preg_match($this->re, $endpoints) == 1) {
            return true;
        }
        return false;
    }
}
