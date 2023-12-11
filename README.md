# CHECK24-CP New Relic Bundle

## Introduction

This bundle provides an enhanced interaction with NewRelic 
from within a Symfony environment, ensuring optimal monitoring 
and logging for your applications.

## Features

- **Transactions:** Automatically push transactions to New Relic.
- **Transaction Naming Strategies:** Configurable strategies for both HTTP requests and CLI (eg: messenger).
- **Logs:** Push logs to NewRelic, linking them together using `traceId`. The bundle also provides batching for optimal performance, configurable via the `logging.buffer_size`.
- **Transaction Exclusion:** Exclude specific transactions based on Symfony route name, path, or even commands.
- **Exception Exclusion:** Exclude specific exceptions to prevent them from appearing in NewRelic Error inbox, e.g., `HttpExceptions`.

## Installation

To install the CHECK24 New Relic bundle, use composer:

```bash
composer require check24-cp/newrelic-bundle
```

## Configuration

After installing the bundle, you need to configure it to suit your application's needs. Here's a detailed explanation of each configuration option based on the provided extension:

### Basic Configuration

- **appname:** The application name. If not set, it defaults to `ini_get('newrelic.appname')`.
- **license:** Your NewRelic license. If not set, defaults to `ini_get('newrelic.license')`.
- **xmit:** A boolean indicating if data should be transmitted to NewRelic immediately, defaults to `false`.
- **interactor:** The service ID of the NewRelic interactor used to communicate with the agent. Defaults to `check24.new_relic.interactor`.
- **logging.buffer_size:** Determines the number of log entries to buffer before sending them to NewRelic in bulk. The range is from 1 to 1000, with a default of 100.

### Transaction Naming

- **transaction_naming.messenger:** Service ID for the strategy used to name messenger transactions. Defaults to `check24.new_relic.transaction_name.messenger.message_name`.
- **transaction_naming.request:** Service ID for the strategy used to name HTTP request transactions. Defaults to `check24.new_relic.transaction_name.request.route_name`.

### Exclusions

- **excluded_transactions.commands:** An array of CLI commands to exclude from NewRelic.
- **excluded_transactions.routes:** An array of Symfony route names to exclude from NewRelic.
- **excluded_transactions.paths:** An array of HTTP paths to exclude from NewRelic.
- **excluded_exceptions:** An array of exception classes to exclude from NewRelic's error inbox.

### Trace ID

- **trace_id_factory:** Service ID for the factory responsible for creating a unique trace ID for each request or message. Defaults to `check24.new_relic.trace_id.uuid_factory`.

## Example Configuration using Symfony Yaml

Here's a basic example of how you might configure the bundle using Symfony's yaml:

```yaml
check24_new_relic:
    appname: "My Symfony App"
    license: "YOUR_NEWRELIC_LICENSE_KEY"
    xmit: false
    interactor: 'custom.new_relic.interactor'
    logging:
        buffer_size: 200
    transaction_naming:
        messenger: 'custom.transaction_name.messenger'
        request: 'custom.transaction_name.request'
    excluded_transactions:
        commands:
            - 'app:exclude-this-command'
        routes:
            - 'exclude_route_name'
        paths:
            - '/exclude-this-path'
    excluded_exceptions:
        - 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'
    trace_id_factory: 'custom.trace_id.factory'

when@dev:
    check24_new_relic:
      # This is useful to be able to get logs about different NewRelic events in Symfony's profiler
      interactor: 'check24.new_relic.logging_interactor'
```

Adjust the values as per your requirements. Ensure you replace placeholders like `YOUR_NEWRELIC_LICENSE_KEY` with actual values.

## Integration with Monolog

One of the standout features of this bundle is the ability to seamlessly integrate logging with NewRelic,
linking them via a `trace.id`. This ensures you have all the necessary context when diagnosing issues, as you can see related
logs in NewRelic that correspond to a particular request or process.
It eliminates the disconnect often seen when logs and monitoring are handled separately. 
In times of issues or outages, having this cohesive view can be invaluable, 
allowing for rapid diagnosis and resolution.

To harness this logging integration, you need to configure your `monolog`
settings specifically to support the `NewRelicHandler` provided by this bundle.

Update your `config/packages/monolog.yaml` with the following configuration:

```yaml
monolog:
    handlers:
        main:
            type: fingers_crossed
            action_level: error
            handler: nested
            excluded_http_codes:
                - 404
                - 405
            channels: ['!event']
        nested:
            type: service
            id: 'check24.new_relic.monolog_handler'
```

## Integration with Messenger

To be able to track messages received with Messenger, you need to update
`config/packages/messenger` with the following configuration:

```yaml
framework:
    messenger:
        buses:
            default:
                middleware:
                    - check24.new_relic.messenger_middleware
```

```yaml
check24_new_relic:
    # ...
    excluded_transactions:
        # Messenger command should be excluded to not interfere the transaction started by Messenger's middleware.
        commands:
            - 'messenger:consume'
```

Now the bundle should be able to report each consumed message as a separate transaction, 
using the message name (see Configuration/Transaction Naming section) as the transaction name.
