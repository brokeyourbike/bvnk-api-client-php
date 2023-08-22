# bvnk-api-client-php

[![Latest Stable Version](https://img.shields.io/github/v/release/brokeyourbike/bvnk-api-client-php)](https://github.com/brokeyourbike/bvnk-api-client-php/releases)
[![Total Downloads](https://poser.pugx.org/brokeyourbike/bvnk-api-client/downloads)](https://packagist.org/packages/brokeyourbike/bvnk-api-client)
[![Maintainability](https://api.codeclimate.com/v1/badges/f45a596e5a4f4a9631ac/maintainability)](https://codeclimate.com/github/brokeyourbike/bvnk-api-client-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/f45a596e5a4f4a9631ac/test_coverage)](https://codeclimate.com/github/brokeyourbike/bvnk-api-client-php/test_coverage)

BVNK API Client for PHP

## Installation

```bash
composer require brokeyourbike/bvnk-api-client
```

## Usage

```php
use BrokeYourBike\Bvnk\Client;
use BrokeYourBike\Bvnk\Interfaces\ConfigInterface;

assert($config instanceof ConfigInterface);
assert($httpClient instanceof \GuzzleHttp\ClientInterface);

$apiClient = new Client($config, $httpClient);
$apiClient->payout($transaction);
```

## Authors
- [Ivan Stasiuk](https://github.com/brokeyourbike) | [Twitter](https://twitter.com/brokeyourbike) | [LinkedIn](https://www.linkedin.com/in/brokeyourbike) | [stasi.uk](https://stasi.uk)

## License
[Mozilla Public License v2.0](https://github.com/brokeyourbike/bvnk-api-client-php/blob/main/LICENSE)
