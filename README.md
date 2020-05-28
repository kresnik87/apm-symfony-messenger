# Elastic APM for Symfony Messenger

This library supports Span traces of [Symfony Messenger](https://github.com/symfony/messenger) messages.

## Installation

1) Install via [composer](https://getcomposer.org/)

    ```shell script
    composer require pccomponentes/apm-symfony-messenger
    ```

## Usage

In all cases, an already created instance of [ElasticApmTracer](https://github.com/zoilomora/elastic-apm-agent-php) is assumed.

### Native PHP

```php
<?php
declare(strict_types=1);

class TextNameExtractor implements PcComponentes\ElasticAPM\Symfony\Component\Messenger\NameExtractor
{
    public function execute($message): string
    {
        if (false === is_string($message)) {
            throw new InvalidArgumentException('The parameter must be of type string');
        }

        return $message;
    }
}

$apmMiddleware = new PcComponentes\ElasticAPM\Symfony\Component\Messenger\ApmMiddleware(
    $apmTracer, /** \ZoiloMora\ElasticAPM\ElasticApmTracer instance. */
    new TextNameExtractor(),
);

$bus = new Symfony\Component\Messenger\MessageBus([
    $apmMiddleware
]);
```

### Service Container (Symfony)

```yaml
app.bus:
  class: Symfony\Component\Messenger\MessageBus
  arguments:
    $middlewareHandlers:
      - '@app.bus.middleware.apm'

app.bus.middleware.apm:
  class: PcComponentes\ElasticAPM\Symfony\Component\Messenger\ApmMiddleware
  arguments:
    $elasticApmTracer: '@apm.tracer' # \ZoiloMora\ElasticAPM\ElasticApmTracer instance.
    $nameExtractor: '@app.bus.middleware.apm.name_extractor'

app.bus.middleware.apm.name_extractor:
  class: ExampleTextNameExtractor
```

## License
Licensed under the [MIT license](http://opensource.org/licenses/MIT)

Read [LICENSE](LICENSE) for more information
