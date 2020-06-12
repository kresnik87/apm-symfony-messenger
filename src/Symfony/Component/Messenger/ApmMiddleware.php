<?php
declare(strict_types=1);

namespace PcComponentes\ElasticAPM\Symfony\Component\Messenger;

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
        if (false === $this->elasticApmTracer->active()) {
            return $stack->next()->handle($envelope, $stack);
        }

        $name = $this->nameExtractor->execute(
            $envelope->getMessage()
        );

        $span = null;

        try {
            $span = $this->elasticApmTracer->startSpan(
                $name,
                'message',
                null,
                null,
                null,
                self::STACKTRACE_SKIP
            );
        } catch (\Throwable $exception) {
            //nothing
        }

        $transaction = $this->elasticApmTracer->startTransaction(
            $name,
            'message'
        );

        try {
            $envelope = $stack->next()->handle($envelope, $stack);

            $transaction->stop('OK');
            if (null !== $span) {
                $span->stop();
            }
        } catch (\Throwable $throwable) {
            $this->elasticApmTracer->captureException($throwable);

            $transaction->stop('KO');
            if (null !== $span) {
                $span->stop();
            }

            throw $throwable;
        }

        return $envelope;
    }
}
