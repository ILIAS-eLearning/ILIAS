<?php declare(strict_types=1);

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
 
namespace ILIAS\Tests\Setup\Metrics;

use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use PHPUnit\Framework\TestCase;

class StorageOnPathWrapperTest extends TestCase
{
    const PATH = "path";

    public function setUp() : void
    {
        $this->storage = $this->createMock(Metrics\Storage::class);
        $this->wrapper = new Metrics\StorageOnPathWrapper(self::PATH, $this->storage);
    }

    public function testStoresToPath() : void
    {
        $key = "key";
        $m = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc");

        $this->storage->expects($this->once())
            ->method("store")
            ->with(self::PATH . "." . $key, $m);

        $this->wrapper->store($key, $m);
    }
}
