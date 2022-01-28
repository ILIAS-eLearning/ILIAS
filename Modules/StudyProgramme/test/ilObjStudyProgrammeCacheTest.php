<?php declare(strict_types=1);

/* Copyright (c) 2021 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilObjStudyProgrammeCacheTest extends TestCase
{
    public function testCreateByConstructor() : void
    {
        $this->expectException(Error::class);
        new ilObjStudyProgrammeCache();
    }

    public function testCreateBySingelton() : void
    {
        $obj = ilObjStudyProgrammeCache::singleton();
        $this->assertInstanceOf(ilObjStudyProgrammeCache::class, $obj);
    }
}
