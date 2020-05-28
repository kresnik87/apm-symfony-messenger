<?php
declare(strict_types=1);

namespace Pcc\ApiLegacyBridge\Util\ElasticAPM\Messenger;

interface NameExtractor
{
    public function execute($message): string;
}
