<?php

declare(strict_types=1);

namespace BestIt\Messenger\Tests\Model;

use BestIt\Messenger\Model\CustomObjectCreated;
use Commercetools\Core\Model\CustomObject\CustomObject;
use PHPUnit\Framework\TestCase;

/**
 * Test custom object created model
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger\Tests\Model
 */
class CustomObjectCreatedTest extends TestCase
{
    /**
     * Test that model extends the origin custom object
     *
     * @return void
     */
    public function testExtendCustomObject(): void
    {
        static::assertInstanceOf(CustomObject::class, new CustomObjectCreated());
    }
}
