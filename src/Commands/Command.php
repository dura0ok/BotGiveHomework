<?php


namespace App\Commands;

interface Command
{
    public function execute(array $object): array;

    public function accept($message): bool;
}
