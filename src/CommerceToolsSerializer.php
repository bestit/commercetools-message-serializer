<?php

declare(strict_types=1);

namespace BestIt\Messenger;

use Commercetools\Core\Model\Common\Resource;
use Commercetools\Core\Model\Message\Message;
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

        $message = null;
        if ($class && is_a($class, Message::class, true)) {
            $message = Message::fromArray(json_decode($encodedEnvelope['body'], true));
        } else {
            $message = new $class(json_decode($encodedEnvelope['body'], true));
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
