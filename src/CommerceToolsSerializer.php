<?php

declare(strict_types=1);

namespace BestIt\Messenger;

use BestIt\Messenger\Exception\DecodeException;
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
        $class = $encodedEnvelope['headers']['X-CommerceTools-Message'] ?? false;
        $body = json_decode($encodedEnvelope['body'], true);
        $type = $body['notificationType'] ?? false;

        $message = null;
        if ($class) {
            $message = new $class($body);
        } else if($type === 'Message') {
            $message = Message::fromArray($body);
        } else if(in_array($type, ['ResourceCreated', 'ResourceUpdated', 'ResourceDeleted'])) {
            $message = Delivery::fromArray($body);
        } else {
            throw new DecodeException(sprintf('Unable to decode unknown type `%s`.', $type));
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
}
