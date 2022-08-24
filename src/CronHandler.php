<?php


namespace App;


use App\Commands\GiveHomeworkCommand;
use App\Commands\HomeworkTrait;
use Exception;
use Friday14\Mailru\Cloud;
use SplFileObject;
use VK\Client\VKApiClient;
use WordsToNumbers\WordsToNumbers;

class CronHandler
{
    use HomeworkTrait;
    /**
     * @var VKApiClient
     */
    private $vk;
    private $token;

    public function __construct($token, $host)
    {
        $this->vk = new VKAPIClient();
        $this->token = $token;
        $this->host = $host;
    }

    public function start()
    {
        $this->mailings();
        $this->attachments();
        $this->voices();
    }

    public function mailings()
    {
        $row = DB::run("SELECT COUNT(*) AS count FROM mailings")->fetch();
        if ($row['count'] > 0) {
            $giveHomework = new GiveHomeworkCommand();
            $message = $giveHomework->execute()['message'];
            $recipients = DB::run("SELECT * FROM recipients")->fetchAll();
            foreach ($recipients as $recipient) {
                $params = [
                    'peer_id' => $recipient['user_id'],
                    'random_id' => mt_rand(1, 9999),
                    'message' => 'Привет, ' . $recipient['name'] . "! " . $message
                ];
                try {
                    $this->vk->messages()->send($this->token, $params);
                } catch(Exception $e){
                    echo $e->getMessage()." || Ошибка при отправке сообщения пользователю: {$recipient['name']} || Ссылка на страницу https://vk.com/id{$recipient['user_id']} ";
                }
            }
        }
            DB::run("delete from mailings order by id desc limit 1");

    }


    public function attachments()
    {
        $text = "";
        $row = DB::run("SELECT COUNT(*) AS count FROM heap")->fetch();
        if ($row['count'] > 0) {
            $heap = DB::run("SELECT * FROM heap")->fetchAll();
            foreach ($heap as $item) {
                foreach (unserialize($item['attachments']) as $attach) {
                    $attach_copy = strtok($attach, '?');
                    $imageName = md5(basename($attach_copy));
                    $extension = pathinfo(basename($attach_copy), PATHINFO_EXTENSION);

                    $imageName = $imageName.".".$extension;
                    $image = __DIR__ . '/../public/images/' . $imageName;

                    file_put_contents($image, file_get_contents($attach));
                    $link = $this->host."/public/images/".$imageName;

                    $cc = $this->vk->utils()->getShortLink($this->token, ['url' => $link]);
                    $text = $text . "\n" . $cc['short_url'];
                }
                $temp = $text;
                $values = ['text' => $item['text'] . $text, 'todate' => $item['todate']];
                $stmt = DB::prepare("INSERT INTO homework (text, todate) VALUES (:text, :todate)");
                $stmt->execute($values);
                $params = [
                    'peer_id' => $item['user_id'],
                    'random_id' => mt_rand(1, 9999),
                    'message' => "Ваше домашнее задание добавлено: " . $temp
                ];
                $this->vk->messages()->send($this->token, $params);
                DB::run("DELETE FROM heap where id = ?", [$item['id']]);
                $text = "";
            }
        }
    }

    public function voices()
    {
        $row = DB::run("SELECT COUNT(*) AS count FROM voiceheap")->fetch();
        if ($row['count'] > 0) {
            $numberTransformer = WordsToNumbers::getWordsTransformer('ru');
            $headers[] = 'Authorization: Bearer VK6WEL4FEEWGIGBGRFSJY6WAPEELJ4TE';
            $headers[] = 'Content-Type: audio/mpeg3';
            $voiceHeap = DB::run("SELECT * FROM voiceheap")->fetchAll();
            foreach ($voiceHeap as $voice) {
                if ($voice['text'] == null) {
                    $values = [];
                    $img = __DIR__ . '/../public/voices/' . basename($voice['link']);
                    file_put_contents($img, file_get_contents($voice['link']));
                    $request = curl_init('https://api.wit.ai/speech');

                    curl_setopt($request, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($request, CURLOPT_POSTFIELDS, file_get_contents(__DIR__ . '/../public/voices/' . basename($voice['link'])));
                    curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

                    $return = curl_exec($request);
                    $return = json_decode($return, true);
                    $return = $return['text'];
                    $return = str_replace('на дату', 'надату', $return);
                    $arr = explode('надату', $return);
                    $date = trim($arr[1]);
                    $text = trim($arr[0]);
                    if (strpos($date, 'точка') !== false) {
                        $date = explode('точка', $date);
                        $day = $numberTransformer->toNumbers($date[0]);
                        $month = $numberTransformer->toNumbers($date[1]);
                        $date = $day . "." . $month;
                    } else {
                        $date = $numberTransformer->toNumbers($date);
                    }
                    $response = $this->recognizeDate($text . " надату " . $date, "надату");
                    $values['text'] = $response['text'];
                    $values['id'] = $voice['id'];
                    $values['todate'] = $response['todate'] ?? null;
                    $stmt = DB::prepare("UPDATE voiceheap SET text=:text, todate = :todate, text = :text WHERE id = :id");
                    $stmt->execute($values);
                    $message = "Мы распознали сообщение: " . $response['text'] . "\n А так же распознали вашу дату " . $response['todate'] . "\nЕсли вы согласны с моим распознованием,то напишите !да " . $voice['id'] . "\n или !да " . $voice['id'] . " надату дата,если мы распознали дату неправильно";
                    $params = ['peer_id' => $voice['user_id'], 'random_id' => mt_rand(1, 9999), 'message' => $message];
                    $this->vk->messages()->send($this->token, $params);
                }
            }
        }
    }
}