<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk\Tests;

use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;
use BrokeYourBike\Bvnk\Interfaces\ConfigInterface;
use BrokeYourBike\Bvnk\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class ClientTest extends TestCase
{
    /** @test */
    public function it_implemets_http_client_interface(): void
    {
        /** @var ConfigInterface */
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        /** @var \GuzzleHttp\ClientInterface */
        $mockedHttpClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();

        $client = new Client($mockedConfig, $mockedHttpClient);

        $this->assertInstanceOf(HttpClientInterface::class, $client);
        $this->assertSame($mockedConfig, $client->getConfig());
    }

    /** @test */
    public function it_uses_http_client_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(HttpClientTrait::class, $usedTraits);
    }

    /** @test */
    public function it_uses_resolve_uri_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(ResolveUriTrait::class, $usedTraits);
    }

    /** @test */
    public function it_uses_has_source_model_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(HasSourceModelTrait::class, $usedTraits);
    }
}
