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

namespace ILIAS\Tests\Setup\Artifact;

use ILIAS\Setup\Artifact;
use PHPUnit\Framework\TestCase;

class ArrayArtifactTest extends TestCase
{
    public function testSerialize(): void
    {
        $data = [
            "one" => 1,
            "two" => 2,
            "nested" => [
                "array" => "are nice"
            ]
        ];

        $a = new Artifact\ArrayArtifact($data);

        $serialized = $a->serialize();

        $this->assertEquals($data, eval("?>" . $serialized));
    }

    public function testOnlyPrimitives(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [ $this ];

        new Artifact\ArrayArtifact($data);
    }
}
