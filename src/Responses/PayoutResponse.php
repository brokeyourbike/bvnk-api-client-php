<?php

// Copyright (C) 2023 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\Bvnk\Responses;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use BrokeYourBike\DataTransferObject\JsonResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class PayoutResponse extends JsonResponse
{
    public ?string $status;
    public ?string $uuid;

    /** @var \BrokeYourBike\Bvnk\Responses\Error[] */
    #[CastWith(ArrayCaster::class, Error::class)]
    public ?array $errorList;
}

class Error extends DataTransferObject
{
    public string $parameter;
    public string $code;
    public string $message;
}
