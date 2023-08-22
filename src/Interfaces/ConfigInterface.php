<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk\Interfaces;

use BrokeYourBike\Bvnk\Enums\EntityTypeEnum;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
interface ConfigInterface
{
    public function getUrl(): string;
    public function getAuthId(): string;
    public function getAuthKey(): string;
    public function getMerchantId(): string;

    public function getSenderType(): EntityTypeEnum;
    public function getSenderCompanyName(): string;
    public function getSenderCountry(): string;
}
