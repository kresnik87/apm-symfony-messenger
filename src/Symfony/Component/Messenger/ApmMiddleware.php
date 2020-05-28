<?php
declare(strict_types=1);

namespace Pcc\ApiLegacyBridge\Util\ElasticAPM\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use ZoiloMora\ElasticAPM\ElasticApmTracer;

final class ApmMiddleware implements MiddlewareInterface
{
    private const STACKTRACE_SKIP = 4;

    private ElasticApmTracer $elasticApmTracer;
    private NameExtractor $nameExtractor;

    public function __construct(
        ElasticApmTracer $elasticApmTracer,
        NameExtractor $nameExtractor
    ) {
        $this->elasticApmTracer = $elasticApmTracer;
        $this->nameExtractor = $nameExtractor;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $name = $this->nameExtractor->execute(
            $envelope->getMessage()
        );

        $span = $this->elasticApmTracer->startSpan(
            $name,
            'message',
            null,
            null,
            null,
            self::STACKTRACE_SKIP
        );

        $transaction = $this->elasticApmTracer->startTransaction(
            $name,
            'message'
        );

        try {
            $envelope = $stack->next()->handle($envelope, $stack);

            $transaction->stop('OK');
            $span->stop();
        } catch (\Throwable $throwable) {
            $this->elasticApmTracer->captureException($throwable);

            $transaction->stop('KO');
            $span->stop();

            throw $throwable;
        }

        return $envelope;
    }
}
