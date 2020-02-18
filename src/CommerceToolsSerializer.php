<?php

declare(strict_types=1);

namespace BestIt\Messenger;

use BestIt\Messenger\Exception\DecodeException;
use BestIt\Messenger\Model\CategoryCreated;
use BestIt\Messenger\Model\CategoryDeleted;
use BestIt\Messenger\Model\CategoryUpdated;
use BestIt\Messenger\Model\CustomerCreated;
use BestIt\Messenger\Model\CustomerDeleted;
use BestIt\Messenger\Model\CustomerUpdated;
use BestIt\Messenger\Model\ProductCreated;
use BestIt\Messenger\Model\ProductDeleted;
use BestIt\Messenger\Model\ProductUpdated;
use Commercetools\Core\Model\Common\Resource;
use Commercetools\Core\Model\Message\Message;
use Commercetools\Core\Model\Subscription\Delivery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Serializer for CommerceTools messages
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger
 */
class CommerceToolsSerializer implements SerializerInterface
{
    /**
     * Decode message object or CommerceTools resource object when given message is not a message
     *
     * @param array $encodedEnvelope
     *
     * @return Envelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        $class = $encodedEnvelope['headers']['X-CommerceTools-Message'] ?? null;
        $body = json_decode($encodedEnvelope['body'], true);
        $type = $body['notificationType'] ?? null;

        $message = null;
        if ($class) {
            $message = new $class($body);
        } else if($type === 'Message') {
            $message = Message::fromArray($body);
        } else if(is_string($type) && in_array($type, ['ResourceCreated', 'ResourceUpdated', 'ResourceDeleted'])) {
            $message = $this->createDeliveryInstance($type, $body['resource']['typeId'], $body);
        } else {
            throw new DecodeException(sprintf('Unable to decode unknown notification type `%s`.', $type));
        }

        return new Envelope($message);
    }

    /**
     * Encode message or resource object
     *
     * @param Envelope $envelope
     *
     * @return array
     */
    public function encode(Envelope $envelope): array
    {
        /** @var Message|Resource $message */
        $message = $envelope->getMessage();

        $encodedEnvelope['headers'] = [
            'X-CommerceTools-Message' => get_class($message)
        ];

        $encodedEnvelope['body'] = json_encode($message->toArray());

        return $encodedEnvelope;
    }

    /**
     * Create delivery instance
     *
     * @param string $notificationType
     * @param string $typeId
     * @param array $body
     *
     * @return Delivery
     */
    private function createDeliveryInstance(string $notificationType, string $typeId, array $body): Delivery
    {
        $map = [
            'ResourceCreated' => [
                'product' => ProductCreated::class,
                'category' => CategoryCreated::class,
                'customer' => CustomerCreated::class
            ],
            'ResourceUpdated' => [
                'product' => ProductUpdated::class,
                'category' => CategoryUpdated::class,
                'customer' => CustomerUpdated::class
            ],
            'ResourceDeleted' => [
                'product' => ProductDeleted::class,
                'category' => CategoryDeleted::class,
                'customer' => CustomerDeleted::class
            ]
        ];

        $class = $map[$notificationType][$typeId] ?? false;
        if (!$class) {
            throw new DecodeException(sprintf('Unable to decode unknown type id `%s`.', $typeId));
        }

        return new $class($body);
    }
}
