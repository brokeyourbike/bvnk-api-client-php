<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk\Tests;

use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\Bvnk\Responses\PayoutResponse;
use BrokeYourBike\Bvnk\Interfaces\TransactionInterface;
use BrokeYourBike\Bvnk\Interfaces\ConfigInterface;
use BrokeYourBike\Bvnk\Enums\EntityTypeEnum;
use BrokeYourBike\Bvnk\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class PayoutTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getRecipientType')->willReturn(EntityTypeEnum::INDIVIDUAL);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getSenderType')->willReturn(EntityTypeEnum::BUSINESS);

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "status": "PENDING",
                "uuid": "193232e0-ca52-4332-a4af-ec4757dc6385"
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * */
        $api = new Client($mockedConfig, $mockedClient);

        $requestResult = $api->payout('nonce123', $transaction);
        $this->assertInstanceOf(PayoutResponse::class, $requestResult);
        $this->assertEquals('PENDING', $requestResult->status);
        $this->assertEquals('193232e0-ca52-4332-a4af-ec4757dc6385', $requestResult->uuid);
    }

    /** @test */
    public function it_can_decode_failed_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getRecipientType')->willReturn(EntityTypeEnum::INDIVIDUAL);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getSenderType')->willReturn(EntityTypeEnum::BUSINESS);

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "errorList": [
                    {
                        "parameter": "country of residence",
                        "code": "invalidCountryCode",
                        "message": "The \'NGs\' is not a valid ISO country code."
                    }
                ]
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * */
        $api = new Client($mockedConfig, $mockedClient);

        $requestResult = $api->payout('nonce123', $transaction);
        $this->assertInstanceOf(PayoutResponse::class, $requestResult);
        $this->assertCount(1, $requestResult->errorList);
        $this->assertEquals('invalidCountryCode', $requestResult->errorList[0]->code);
    }
}
