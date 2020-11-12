<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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

    public function testStoresToPath()
    {
        $key = "key";
        $m = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc");

        $this->storage->expects($this->once())
            ->method("store")
            ->with(self::PATH . "." . $key, $m);

        $this->wrapper->store($key, $m);
    }
}
