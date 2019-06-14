<?php

declare(strict_types=1);

namespace BestIt\Messenger\Tests;

use BestIt\Messenger\CommerceToolsSerializer;
use Commercetools\Core\Model\Message\OrderCreatedMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

/**
 * Test message serializer
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger\Tests
 */
class CommerceToolsSerializerTest extends TestCase
{
    /**
     * Test decode
     *
     * @return void
     */
    public function testDecode(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'headers' => [
                'X-CommerceTools-Message' => OrderCreatedMessage::class
            ],
            'body' => json_encode(new OrderCreatedMessage())
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(OrderCreatedMessage::class, $envelope->getMessage());
    }

    /**
     * Test encode
     *
     * @return void
     */
    public function testEncode(): void
    {
        $serializer = new CommerceToolsSerializer();

        $order = new OrderCreatedMessage();
        $order->setId('FOOBAR');

        $envelope = new Envelope($order);
        $encodedEnvelope = $serializer->encode($envelope);

        static::assertEquals([
            'X-CommerceTools-Message' => OrderCreatedMessage::class
        ], $encodedEnvelope['headers']);
        static::assertEquals(json_encode($order), $encodedEnvelope['body']);
    }
}
