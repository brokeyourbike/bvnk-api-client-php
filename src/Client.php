<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk;

use GuzzleHttp\ClientInterface;
use Carbon\Carbon;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;
use BrokeYourBike\Bvnk\Responses\StatusResponse;
use BrokeYourBike\Bvnk\Responses\PayoutResponse;
use BrokeYourBike\Bvnk\Interfaces\TransactionInterface;
use BrokeYourBike\Bvnk\Interfaces\ConfigInterface;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class Client implements HttpClientInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function payout(string $nonce, TransactionInterface $transaction): PayoutResponse
    {
        $method = HttpMethodEnum::POST->value;
        $uri = $this->prepareUri('api/wallet/payout');

        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'X-Idempotency-Key' => $nonce,
                'Authorization' => $this->hawkHeader($method, $uri),
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'merchantId' => $this->config->getMerchantId(),
                'amount' => $transaction->getAmount(),
                'paymentReference' => $transaction->getReference(),
                'payoutDetails' => [
                    'type' => $transaction->getRecipientType()->value,
                    'accountNumber' => $transaction->getAccountNumber(),
                    'code' => $transaction->getBankCode(),
                    'firstName' => $transaction->getRecipientFirstName(),
                    'lastName' => $transaction->getRecipientLastName(),
                    'countryOfResidence' => $transaction->getRecipientCountry(),
                ],
                'ultimateSenderDetails' => [
                    'type' => $this->config->getSenderType()->value,
                    'companyName' => $this->config->getSenderCompanyName(),
                    'countryOfBusinessRegistration' => $this->config->getSenderCountry(),
                ],
            ],
        ];

        if ($transaction instanceof SourceModelInterface){
            $options[\BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL] = $transaction;
        }

        $response = $this->httpClient->request($method, $uri, $options);
        return new PayoutResponse($response);
    }
    
    public function status(string $nonce, string $uuid): StatusResponse
    {
        $method = HttpMethodEnum::GET->value;
        $uri = $this->prepareUri("api/wallet/payout/{$uuid}");

        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'X-Idempotency-Key' => $nonce,
                'Authorization' => $this->hawkHeader($method, $uri),
            ],
        ];

        $response = $this->httpClient->request($method, $uri, $options);
        return new StatusResponse($response);
    }

    private function prepareUri(string $path): string
    {
        return (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), $path);
    }

    /**
     * This function creates a string of random alphanumeric characters
     */
    private function generateNonce(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * This function generates a normalized string out of the header options
     */
    private function generateNormalizedString(string $type, array $options): string
    {
        $headerVersion = "1";

        $normalized = "hawk." .
            $headerVersion . "." .
            $type . "\n" .
            $options['ts'] . "\n" .
            $options['nonce'] . "\n" .
            strtoupper($options['method']) . "\n" .
            $options['resource'] . "\n" .
            strtolower($options['host']) . "\n" .
            $options['port'] . "\n" .
            ($options['hash'] ?? '') . "\n";

        $normalized .= "\n";

        return $normalized;
    }

    /**
     * This function generates the Hawk authentication header
     * @throws Exception Will throw an exception if the provided arguments are of an invalid type.
     */
    private function hawkHeader(string $method, string $uri): string
    {
        // Application time
        $timestamp = Carbon::now()->timestamp;

        // Calculate signature to hash
        $artifacts = [
            'ts' => $timestamp,
            'nonce' => $this->generateNonce(6),
            'method' => $method,
            'resource' => parse_url($uri, PHP_URL_PATH) . (parse_url($uri, PHP_URL_QUERY) ? '?' . parse_url($uri, PHP_URL_QUERY) : ''),
            'host' => parse_url($uri, PHP_URL_HOST),
            'port' => parse_url($uri, PHP_URL_PORT) ?? (parse_url($uri, PHP_URL_SCHEME) === "http" ? 80 : 443),
        ];

        // Generate the MAC
        $normalized = $this->generateNormalizedString("header", $artifacts);
        $mac = base64_encode(hash_hmac('sha256', $normalized, $this->config->getAuthKey(), true));

        // Construct Authorization header
        $header = 'Hawk id="' . $this->config->getAuthId() . '", ts="' . $artifacts['ts'] . '", nonce="' . $artifacts['nonce'] . '", mac="' . $mac . '"';

        return $header;
    }
}
