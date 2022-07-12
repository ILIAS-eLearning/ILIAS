<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilSCORMObjectTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilSCORMObjectTest extends TestCase
{
    public function testManifestImportId() : void
    {
        $manifest = new ilSCORMManifest();
        $testImportId = str_shuffle(uniqid('abcdefgh', true));
        $manifest->setImportId($testImportId);

        $this->assertEquals($manifest->getTitle(), $manifest->getImportId());
    }
}
