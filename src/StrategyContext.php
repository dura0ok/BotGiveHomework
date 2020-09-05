<?php

namespace App;

class StrategyContext
{
    private $strategies;

    public function __construct(...$strategies)
    {
        $this->strategies = $strategies;
    }

    public function run($data): array
    {
        $object = $data['object'];
        $response = [];
        $message = $this->clearMessage($object['text']);
        $object['text'] = $message;
        foreach ($this->strategies as $strategy) {
            if ($strategy->accept($message)) {
                $response = $strategy->execute($object);
            }
        }
        return $response;
    }

    public function clearMessage(string $message = ""): string
    {
        $message = mb_strtolower($message);
        $message = preg_replace('/\[.*?\]/', '', $message);
        if ($message != "" && $message[0] == ',') {
            unset($message[0]);
        }
        $message = ltrim($message);
        return $message;
    }
}
