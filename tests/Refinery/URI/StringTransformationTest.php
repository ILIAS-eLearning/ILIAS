<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\URI;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\URI;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\URI\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

class StringTransformationTest extends TestCase
{
    /**
     * @var StringTransformation
     */
    private $transformation;

    public function setUp() : void
    {
        $this->transformation = new StringTransformation();
    }

    public function testSimpleUri()
    {
        $uri              = new URI('http://ilias.de');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de', $transformedValue);
    }

    public function testUriWithPath()
    {
        $uri              = new URI('http://ilias.de/with/path');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/with/path', $transformedValue);
    }

    public function testUriWithFragment()
    {
        $uri              = new URI('http://ilias.de/test.php#title');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/test.php#title', $transformedValue);
    }

    public function testSimpleUriWithQueryParameter()
    {
        $uri              = new URI('http://ilias.de?test=something&further=1');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de?test=something&further=1', $transformedValue);
    }

    public function testUriWithQueryPathAndParameter()
    {
        $uri              = new URI('http://ilias.de/with/path?test=something&further=1');
        $transformedValue = $this->transformation->transform($uri);

        $this->assertEquals('http://ilias.de/with/path?test=something&further=1', $transformedValue);
    }

    public function testTransformNotURIObjectFails()
    {
        $this->expectException(ConstraintViolationException::class);
        $transformedValue = $this->transformation->transform('http://ilias.de');

        $this->assertEquals('http://ilias.de', $transformedValue);
    }
}
