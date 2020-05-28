<?php
declare(strict_types=1);

namespace PcComponentes\ElasticAPM\Symfony\Component\Messenger;

interface NameExtractor
{
    public function execute($message): string;
}
