<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Artifact;

use ILIAS\Setup\Artifact;
use PHPUnit\Framework\TestCase;

class ArrayArtifactTest extends TestCase
{
    public function testSerialize() : void
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

    public function testOnlyPrimitives() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [ $this ];

        new Artifact\ArrayArtifact($data);
    }
}
