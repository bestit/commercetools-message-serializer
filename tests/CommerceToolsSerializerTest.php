<?php

declare(strict_types=1);

namespace BestIt\Messenger\Tests;

use BestIt\Messenger\Exception\DecodeException;
use BestIt\Messenger\CommerceToolsSerializer;
use BestIt\Messenger\Model\CustomObjectCreated;
use Commercetools\Core\Model\Message\OrderCreatedMessage;
use Commercetools\Core\Model\Subscription\ResourceCreatedDelivery;
use Commercetools\Core\Model\Subscription\ResourceDeletedDelivery;
use Commercetools\Core\Model\Subscription\ResourceUpdatedDelivery;
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
     * Test decode a CommerceTools created delivery
     *
     * @return void
     */
    public function testDecodeDeliveryCreated(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'body' => json_encode(new ResourceCreatedDelivery([
                'notificationType' => 'ResourceCreated',
                'resource' => [
                    'id' => 'foo'
                ]
            ]))
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(ResourceCreatedDelivery::class, $envelope->getMessage());
        static::assertEquals('foo', $envelope->getMessage()->getResource()->getId());
    }

    /**
     * Test decode a CommerceTools deleted delivery
     *
     * @return void
     */
    public function testDecodeDeliveryDeleted(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'body' => json_encode(new ResourceDeletedDelivery([
                'notificationType' => 'ResourceDeleted',
                'resource' => [
                    'id' => 'foo'
                ]
            ]))
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(ResourceDeletedDelivery::class, $envelope->getMessage());
        static::assertEquals('foo', $envelope->getMessage()->getResource()->getId());
    }

    /**
     * Test decode a CommerceTools updated delivery
     *
     * @return void
     */
    public function testDecodeDeliveryUpdated(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'body' => json_encode(new ResourceUpdatedDelivery([
                'notificationType' => 'ResourceUpdated',
                'resource' => [
                    'id' => 'foo'
                ]
            ]))
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(ResourceUpdatedDelivery::class, $envelope->getMessage());
        static::assertEquals('foo', $envelope->getMessage()->getResource()->getId());
    }

    /**
     * Test decode method throws exception
     *
     * @return void
     */
    public function testDecodeException(): void
    {
        $this->expectException(DecodeException::class);

        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'body' => json_encode(new ResourceUpdatedDelivery())
        ];

        $serializer->decode($encodedEnvelope);
    }

    /**
     * Test decode a CommerceTools message
     *
     * @return void
     */
    public function testDecodeMessage(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'body' => json_encode(new OrderCreatedMessage(['notificationType' => 'Message', 'id' => 'foo']))
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(OrderCreatedMessage::class, $envelope->getMessage());
        static::assertEquals('foo', $envelope->getMessage()->getId());
    }

    /**
     * Test decode a CommerceTools resource
     *
     * @return void
     */
    public function testDecodeResource(): void
    {
        $serializer = new CommerceToolsSerializer();

        $encodedEnvelope = [
            'headers' => [
                'X-CommerceTools-Message' => CustomObjectCreated::class
            ],
            'body' => json_encode(new CustomObjectCreated(['id' => 'foo']))
        ];

        $envelope = $serializer->decode($encodedEnvelope);
        static::assertInstanceOf(CustomObjectCreated::class, $envelope->getMessage());
        static::assertEquals('foo', $envelope->getMessage()->getId());
    }

    /**
     * Test encode a CommerceTools message
     *
     * @return void
     */
    public function testEncodeMessage(): void
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

    /**
     * Test encode a CommerceTools resource
     *
     * @return void
     */
    public function testEncodeResource(): void
    {
        $serializer = new CommerceToolsSerializer();

        $customObject = new CustomObjectCreated();
        $customObject->setId('FOOBAR');

        $envelope = new Envelope($customObject);
        $encodedEnvelope = $serializer->encode($envelope);

        static::assertEquals([
            'X-CommerceTools-Message' => CustomObjectCreated::class
        ], $encodedEnvelope['headers']);
        static::assertEquals(json_encode($customObject), $encodedEnvelope['body']);
    }
}
