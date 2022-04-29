<?php
namespace App\Writer;

interface WriterInterface
{
    public function open();

    public function write(array $data);

    public function close();
}
