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
use BestIt\Messenger\Model\InventoryEntryCreated;
use BestIt\Messenger\Model\InventoryEntryDeleted;
use BestIt\Messenger\Model\InventoryEntryUpdated;
use BestIt\Messenger\Model\ProductCreated;
use BestIt\Messenger\Model\ProductDeleted;
use BestIt\Messenger\Model\ProductUpdated;
use BestIt\Messenger\Model\ShoppingListCreated;
use BestIt\Messenger\Model\ShoppingListDeleted;
use BestIt\Messenger\Model\ShoppingListUpdated;
use BestIt\Messenger\Model\SubscriptionCreated;
use BestIt\Messenger\Model\SubscriptionDeleted;
use BestIt\Messenger\Model\SubscriptionUpdated;
use Commercetools\Core\Model\Common\Resource;
use Commercetools\Core\Model\Message\Message;
use Commercetools\Core\Model\Subscription\Delivery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Serializer for CommerceTools messages
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger
 */
class CommerceToolsSerializer implements SerializerInterface
{
    private const STAMP_HEADER_PREFIX = 'X-Message-Stamp-';

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

        $stamps = $this->decodeStamps($encodedEnvelope);

        return new Envelope($message, $stamps);
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

        $encodedEnvelope['headers'] = array_merge(
            [
                'X-CommerceTools-Message' => get_class($message)
            ],
            $this->encodeStamps($envelope->withoutStampsOfType(NonSendableStampInterface::class))
        );

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
                'customer' => CustomerCreated::class,
                'inventory-entry' => InventoryEntryCreated::class,
                'subscription' => SubscriptionCreated::class,
                'shopping-list' => ShoppingListCreated::class,
            ],
            'ResourceUpdated' => [
                'product' => ProductUpdated::class,
                'category' => CategoryUpdated::class,
                'customer' => CustomerUpdated::class,
                'inventory-entry' => InventoryEntryUpdated::class,
                'subscription' => SubscriptionUpdated::class,
                'shopping-list' => ShoppingListUpdated::class,
            ],
            'ResourceDeleted' => [
                'product' => ProductDeleted::class,
                'category' => CategoryDeleted::class,
                'customer' => CustomerDeleted::class,
                'inventory-entry' => InventoryEntryDeleted::class,
                'subscription' => SubscriptionDeleted::class,
                'shopping-list' => ShoppingListDeleted::class,
            ]
        ];

        $class = $map[$notificationType][$typeId] ?? false;
        if (!$class) {
            throw new DecodeException(sprintf('Unable to decode unknown type id `%s`.', $typeId));
        }

        return new $class($body);
    }

    /**
     * Decode all stamps
     *
     * @param array $encodedEnvelope
     *
     * @return array
     */
    private function decodeStamps(array $encodedEnvelope): array
    {
        $stamps = [];

        foreach ($encodedEnvelope['headers'] as $name => $value) {
            if (0 !== strpos($name, self::STAMP_HEADER_PREFIX)) {
                continue;
            }

            $stamps[] = unserialize($value);
        }

        if ($stamps) {
            $stamps = array_merge(...$stamps);
        }

        return $stamps;
    }

    /**
     * Encode all stamps
     *
     * @param Envelope $envelope
     *
     * @return array
     */
    private function encodeStamps(Envelope $envelope): array
    {
        if (!$allStamps = $envelope->all()) {
            return [];
        }

        $headers = [];
        foreach ($allStamps as $class => $stamps) {
            $headers[self::STAMP_HEADER_PREFIX . $class] = serialize($stamps);
        }

        return $headers;
    }
}
