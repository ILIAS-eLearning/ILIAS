<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Identification\UniqueIDIdentificationGenerator;
use ILIAS\ResourceStorage\StorageHandler\FSV1PathGenerator;
use ILIAS\ResourceStorage\StorageHandler\FSV2PathGenerator;
use ILIAS\ResourceStorage\StorageHandler\PathGenerator\UUIDBasedPathGenerator;
use ILIAS\ResourceStorage\StorageHandler\PathGenerator\MaxNestingPathGenerator;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class PathGeneratorTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PathGeneratorTest extends AbstractBaseTest
{
    protected array $prohibited = [
        "<", // (less than)
        ">", // (greater than)
        ":", // (colon - sometimes works, but is actually NTFS Alternate Data Streams)
        "\"", // (double quote)
        "\\", // (backslash)
        "|", // (vertical bar or pipe)
        "?", // (question mark)
        "*", // (asterisk)
    ];

    public function testPathGeneratorV1(): void
    {
        $identification_generator = new UniqueIDIdentificationGenerator();
        $identification = $identification_generator->getUniqueResourceIdentification();

        $path_generator = new UUIDBasedPathGenerator();
        $path = $path_generator->getPathFor($identification);
        $this->assertGreaterThanOrEqual(strlen($identification->serialize()), strlen($path));
        foreach ($this->prohibited as $value) {
            $this->assertFalse(strpos($path, $value));
        }

        $new_identification = $path_generator->getIdentificationFor($path);
        $this->assertEquals($identification->serialize(), $new_identification->serialize());
    }

    public function testPathGeneratorV2(): void
    {
        $identification_generator = new UniqueIDIdentificationGenerator();
        $identification = $identification_generator->getUniqueResourceIdentification();

        $path_generator = new MaxNestingPathGenerator();
        $path = $path_generator->getPathFor($identification);

        foreach ($this->prohibited as $value) {
            $this->assertFalse(strpos($path, $value));
        }

        $new_identification = $path_generator->getIdentificationFor($path);
        $this->assertEquals($identification->serialize(), $new_identification->serialize());
    }
}
