<?php

declare(strict_types=1);

namespace BestIt\Messenger\Tests\Model;

use BestIt\Messenger\Model\CategoryCreated;
use BestIt\Messenger\Model\CategoryDeleted;
use BestIt\Messenger\Model\CategoryUpdated;
use BestIt\Messenger\Model\CustomerCreated;
use BestIt\Messenger\Model\CustomerDeleted;
use BestIt\Messenger\Model\CustomerUpdated;
use BestIt\Messenger\Model\CustomObjectCreated;
use BestIt\Messenger\Model\ProductCreated;
use BestIt\Messenger\Model\ProductDeleted;
use BestIt\Messenger\Model\ProductUpdated;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Model\Subscription\ResourceCreatedDelivery;
use Commercetools\Core\Model\Subscription\ResourceDeletedDelivery;
use Commercetools\Core\Model\Subscription\ResourceUpdatedDelivery;
use PHPUnit\Framework\TestCase;

/**
 * Test that models extend from correct parent
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package BestIt\Messenger\Tests\Model
 */
class ModelTest extends TestCase
{
    /**
     * Test that model extends the origin class
     *
     * @return void
     */
    public function testExtend(): void
    {
        static::assertInstanceOf(CustomObject::class, new CustomObjectCreated());
        
        static::assertInstanceOf(ResourceCreatedDelivery::class, new CategoryCreated());
        static::assertInstanceOf(ResourceDeletedDelivery::class, new CategoryDeleted());
        static::assertInstanceOf(ResourceUpdatedDelivery::class, new CategoryUpdated());

        static::assertInstanceOf(ResourceCreatedDelivery::class, new ProductCreated());
        static::assertInstanceOf(ResourceDeletedDelivery::class, new ProductDeleted());
        static::assertInstanceOf(ResourceUpdatedDelivery::class, new ProductUpdated());

        static::assertInstanceOf(ResourceCreatedDelivery::class, new CustomerCreated());
        static::assertInstanceOf(ResourceDeletedDelivery::class, new CustomerDeleted());
        static::assertInstanceOf(ResourceUpdatedDelivery::class, new CustomerUpdated());
    }
}
