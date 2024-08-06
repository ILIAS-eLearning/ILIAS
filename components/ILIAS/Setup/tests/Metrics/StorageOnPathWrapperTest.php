<?php

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

declare(strict_types=1);

namespace ILIAS\Tests\Setup\Metrics;

use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use ILIAS\Setup\Metrics\MetricType as MT;
use ILIAS\Setup\Metrics\MetricStability as MS;
use PHPUnit\Framework\TestCase;

class StorageOnPathWrapperTest extends TestCase
{
    public const PATH = "path";

    protected Metrics\Storage $storage;
    protected Metrics\StorageOnPathWrapper $wrapper;

    public function setUp(): void
    {
        $this->storage = $this->createMock(Metrics\Storage::class);
        $this->wrapper = new Metrics\StorageOnPathWrapper(self::PATH, $this->storage);
    }

    public function testStoresToPath(): void
    {
        $key = "key";
        $m = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc");

        $this->storage->expects($this->once())
            ->method("store")
            ->with(self::PATH . "." . $key, $m);

        $this->wrapper->store($key, $m);
    }
}
