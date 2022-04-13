<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ForumNotificationCacheTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ForumNotificationCacheTest extends TestCase
{
    public function testExceptionIsRaisedWhenTryingToRetrieveItemNotCachedYet() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $cache = new ilForumNotificationCache();
        $cache->fetch('item');
    }

    public function testCacheItemResultsInCacheHit() : void
    {
        $cache = new ilForumNotificationCache();
        $cache->store('item', 'ilias');

        $this->assertTrue($cache->exists('item'));
        $this->assertSame('ilias', $cache->fetch('item'));
    }

    public function nonScalarValuesProvider() : array
    {
        return [
            'Array Type' => [[4]],
            'Object Type' => [new stdClass()],
            'Ressource Type' => [fopen('php://temp', 'rb')]
        ];
    }

    /**
     * @param $nonScalarValue
     * @dataProvider nonScalarValuesProvider
     */
    public function testExceptionIsRaisedWhenKeyShouldBeBuiltWithNonScalarValues($nonScalarValue) : void
    {
        $this->expectException(InvalidArgumentException::class);

        $cache = new ilForumNotificationCache();
        $key = $cache->createKeyByValues([$nonScalarValue, $nonScalarValue]);
    }

    public function scalarValuesAndNullProvider() : array
    {
        return [
            'Float Type' => [4.0],
            'String Type' => ['4'],
            'Boolean Type' => [false],
            'Integer Type' => [4],
            'Null Type' => [null],
        ];
    }

    /**
     * @param $scalarValue
     * @dataProvider scalarValuesAndNullProvider
     */
    public function testCacheKeyCouldBeGeneratedByArray($scalarValue) : void
    {
        $cache = new ilForumNotificationCache();
        $key = $cache->createKeyByValues([$scalarValue, $scalarValue]);

        $this->assertNotEmpty($key);
        $this->assertSame(32, strlen($key));
    }
}
