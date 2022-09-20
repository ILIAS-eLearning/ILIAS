<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Class ForumNotificationCacheTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ForumNotificationCacheTest extends TestCase
{
    public function testExceptionIsRaisedWhenTryingToRetrieveItemNotCachedYet(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $cache = new ilForumNotificationCache();
        $cache->fetch('item');
    }

    public function testCacheItemResultsInCacheHit(): void
    {
        $cache = new ilForumNotificationCache();
        $cache->store('item', 'ilias');

        $this->assertTrue($cache->exists('item'));
        $this->assertSame('ilias', $cache->fetch('item'));
    }

    public function nonScalarValuesProvider(): array
    {
        return [
            'Array Type' => [[4]],
            'Object Type' => [new stdClass()],
            'Ressource Type' => [fopen('php://temp', 'rb')]
        ];
    }

    /**
     * @param mixed $nonScalarValue
     * @dataProvider nonScalarValuesProvider
     */
    public function testExceptionIsRaisedWhenKeyShouldBeBuiltWithNonScalarValues($nonScalarValue): void
    {
        $this->expectException(InvalidArgumentException::class);

        $cache = new ilForumNotificationCache();
        $cache->createKeyByValues([$nonScalarValue, $nonScalarValue]);
    }

    public function scalarValuesAndNullProvider(): array
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
     * @param scalar|null $scalarValue
     * @dataProvider scalarValuesAndNullProvider
     */
    public function testCacheKeyCouldBeGeneratedByArray($scalarValue): void
    {
        $cache = new ilForumNotificationCache();
        $key = $cache->createKeyByValues([$scalarValue, $scalarValue]);

        $this->assertNotEmpty($key);
        $this->assertSame(32, strlen($key));
    }
}
