<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Message\WebPushSubscribeMessage;
use App\State\WebPushSubscribeProcessor;

#[ApiResource(
    shortName: 'webpush',
    security: 'is_granted("ROLE_USER")',
    description: 'Web push subscription',
    operations: [
        new Post(
            uriTemplate: '/webpush/subscribe',
            input: WebPushSubscribeMessage::class,
            output: false,
            processor: WebPushSubscribeProcessor::class,
            description: 'Subscribe to webpush messages',
            status: 202,
            openapi: new Operation(
                summary: 'Subscribe to webpush messages',
                description: 'Subscribe to webpush messages (Firebase Cloud Messaging)',
                requestBody: new RequestBody(
                    description: 'Firebase token',
                ),
                responses: [
                    '202' => [
                        'description' => 'Subscription request accepted',
                    ],
                ],
            ),
        ),
    ],
)]
class WebPushResource
{
}
