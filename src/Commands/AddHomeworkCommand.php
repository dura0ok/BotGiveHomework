<?php


namespace App\Commands;


class AddHomeworkCommand implements Command
{
    use HomeworkTrait;
    use AdminTrait;

    public function execute(array $object): array
    {
        $res = $this->isAdmin($object['peer_id']);
        if ($res) {
            if (!empty($object['attachments'])) {
                $attachments = [];
                foreach ($object['attachments'] as $attach) {

                    if ($attach['type'] == 'photo') {
                        $maxHeight = 0;
                        $maxWidth = 0;
                        $bigPicture = $attach['photo']['sizes'][0]['url'];
                        foreach ($attach['photo']['sizes'] as $size) {
                            if ($maxHeight < $size['height'] && $maxWidth < $size['width']) {
                                $maxHeight = $size['height'];
                                $maxWidth = $size['width'];
                                $bigPicture = $size['url'];
                            }
                        }
                        array_push($attachments, $bigPicture);
                    }
                }
                $this->saveHomework($object['text'], $attachments, $object['peer_id']);
                return ["message" => "Ожидайте,ваше домашнее задание будет добавлено через в течении 10 минут! \n Мы вам напишем,когда его добавим"];

            } else {
                $this->saveHomework($object['text']);
                return ['message' => "Ваше домашнее задание успешно добавлено!"];
            }
        } else {
            return ["message" => "Ошибка прав доступа"];
        }
    }

    public function accept($message): bool
    {
        if (strpos($message, '!добавитьдз') !== false) {
            return true;
        }
        return false;
    }
}
