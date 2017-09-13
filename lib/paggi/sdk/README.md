# Paggi PHP SDK

This repository contains the software of Paggi PHP SDK.

You can use this SDK to integrate your application with [Paggi API](https://docs.paggi.com)

## Instalation

Use [composer](https://getcomposer.org/) to install this SDK:

```sh
composer require paggi/sdk
```

## Usage

See the example below:

```php
use \Paggi\Paggi;
use \Paggi\Charge;

# First, set your API Key
Paggi::setApiKey('B31DCE74-E768-43ED-86DA-85501612548F');

$charges = Charge::findAll();
$charge  = $charges['result'][0];

$charge->cancel();
```

TO see more details, visit our [API documentation](https://docs.paggi.com/docs)
