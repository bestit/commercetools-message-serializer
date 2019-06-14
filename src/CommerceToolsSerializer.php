<?php

declare(strict_types=1);

namespace BestIt\Messenger;

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
     * Decode message object
     *
     * @param array $encodedEnvelope
     *
     * @return Envelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        return new Envelope(Message::fromArray(json_decode($encodedEnvelope['body'], true)));
    }

    /**
     * Encode message object
     *
     * @param Envelope $envelope
     *
     * @return array
     */
    public function encode(Envelope $envelope): array
    {
        /** @var Message $message */
        $message = $envelope->getMessage();

        $encodedEnvelope['headers'] = [
            'X-CommerceTools-Message' => get_class($message)
        ];

        $encodedEnvelope['body'] = json_encode($message->toArray());

        return $encodedEnvelope;
    }
}
