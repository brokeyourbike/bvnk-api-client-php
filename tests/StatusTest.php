<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk\Tests;

use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\Bvnk\Responses\StatusResponse;
use BrokeYourBike\Bvnk\Interfaces\ConfigInterface;
use BrokeYourBike\Bvnk\Enums\EntityTypeEnum;
use BrokeYourBike\Bvnk\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class StatusTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');
        $mockedConfig->method('getSenderType')->willReturn(EntityTypeEnum::BUSINESS);

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "uuid": "193232e0-ca52-4332-a4af-ec4757dc6385",
                "amount": 150,
                "currency": "NGN",
                "status": "SUCCESSFUL",
                "statusDetail": "The withdrawal is being processed.",
                "merchantId": "5b387dca-7247-4da2-bf03-fd0d76604393",
                "payoutId": "119240",
                "paymentReference": "TEST1692698111",
                "createdAt": "2023-08-22T09:55:14.000+0000",
                "updatedAt": "2023-08-22T11:18:03.225+0000",
                "fee": 50
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * */
        $api = new Client($mockedConfig, $mockedClient);

        $requestResult = $api->status('nonce123', '193232e0-ca52-4332-a4af-ec4757dc6385');
        $this->assertInstanceOf(StatusResponse::class, $requestResult);
        $this->assertEquals('SUCCESSFUL', $requestResult->status);
    }
}
