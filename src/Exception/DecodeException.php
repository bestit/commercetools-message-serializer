<?php

declare(strict_types=1);

namespace BestIt\Messenger\Exception;

use InvalidArgumentException;

/**
 * Exception if decoding failed
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger\Exception
 */
class DecodeException extends InvalidArgumentException
{
}
