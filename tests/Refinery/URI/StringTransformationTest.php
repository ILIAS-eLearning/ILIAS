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

namespace ILIAS\Tests\Refinery\URI;

use ILIAS\Data\URI;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\URI\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

class StringTransformationTest extends TestCase
{
    private StringTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new StringTransformation();
    }

    public function testSimpleUri(): void
    {
        $uri = new URI('http://ilias.de');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de', $transformedValue);
    }

    public function testUriWithPath(): void
    {
        $uri = new URI('http://ilias.de/with/path');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/with/path', $transformedValue);
    }

    public function testUriWithFragment(): void
    {
        $uri = new URI('http://ilias.de/test.php#title');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/test.php#title', $transformedValue);
    }

    public function testSimpleUriWithQueryParameter(): void
    {
        $uri = new URI('http://ilias.de?test=something&further=1');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de?test=something&further=1', $transformedValue);
    }

    public function testUriWithQueryPathAndParameter(): void
    {
        $uri = new URI('http://ilias.de/with/path?test=something&further=1');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/with/path?test=something&further=1', $transformedValue);
    }

    public function testTransformNotURIObjectFails(): void
    {
        $this->expectException(ConstraintViolationException::class);
        $transformedValue = $this->transformation->transform('http://ilias.de');

        $this->assertEquals('http://ilias.de', $transformedValue);
    }
}
