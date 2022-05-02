<?php

namespace ILIAS\HTTP;

/** @noRector */
require_once "AbstractBaseTest.php";
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;

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
 * Class WrapperTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class WrapperTest extends AbstractBaseTest
{
    protected Factory $refinery;
    protected array $get = ['key_one' => 1, 'key_two' => 2];
    protected array $post = ['key_one' => 1, 'key_two' => 2];
    protected array $cookie = ['key_one' => 1, 'key_two' => 2];

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();
        $language = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->refinery = new Factory(new \ILIAS\Data\Factory(), $language);
    }


    public function testWrapperfactory() : void
    {
        $wrapper_factory = new WrapperFactory($this->request_interface);

        // Query
        $this->request_interface->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $wrapper_factory->query();

        // Post
        $this->request_interface->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([]);

        $wrapper_factory->post();

        // Cookie
        $this->request_interface->expects($this->once())
            ->method('getCookieParams')
            ->willReturn([]);

        $wrapper_factory->cookie();
    }

    public function testQuery() : void
    {
        $wrapper_factory = new WrapperFactory($this->request_interface);

        $this->request_interface->expects($this->once())
            ->method('getQueryParams')
            ->willReturn($this->get);

        $query = $wrapper_factory->query();

        $this->assertTrue($query->has('key_one'));
        $this->assertTrue($query->has('key_two'));
        $this->assertFalse($query->has('key_three'));

        $string_trafo = $this->refinery->kindlyTo()->string();
        $int_trafo = $this->refinery->kindlyTo()->int();

        $this->assertEquals("1", $query->retrieve('key_one', $string_trafo));
        $this->assertIsString($query->retrieve('key_one', $string_trafo));

        $this->assertEquals(1, $query->retrieve('key_one', $string_trafo));
        $this->assertIsInt($query->retrieve('key_one', $int_trafo));
    }

    public function testPost() : void
    {
        $wrapper_factory = new WrapperFactory($this->request_interface);

        $this->request_interface->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($this->post);

        $post = $wrapper_factory->post();

        $this->assertTrue($post->has('key_one'));
        $this->assertTrue($post->has('key_two'));
        $this->assertFalse($post->has('key_three'));

        $string_trafo = $this->refinery->kindlyTo()->string();
        $int_trafo = $this->refinery->kindlyTo()->int();

        $this->assertEquals("1", $post->retrieve('key_one', $string_trafo));
        $this->assertIsString($post->retrieve('key_one', $string_trafo));

        $this->assertEquals(1, $post->retrieve('key_one', $string_trafo));
        $this->assertIsInt($post->retrieve('key_one', $int_trafo));
    }

    public function testCookie() : void
    {
        $wrapper_factory = new WrapperFactory($this->request_interface);

        $this->request_interface->expects($this->once())
            ->method('getCookieParams')
            ->willReturn($this->cookie);

        $cookie = $wrapper_factory->cookie();

        $this->assertTrue($cookie->has('key_one'));
        $this->assertTrue($cookie->has('key_two'));
        $this->assertFalse($cookie->has('key_three'));

        $string_trafo = $this->refinery->kindlyTo()->string();
        $int_trafo = $this->refinery->kindlyTo()->int();

        $this->assertEquals("1", $cookie->retrieve('key_one', $string_trafo));
        $this->assertIsString($cookie->retrieve('key_one', $string_trafo));

        $this->assertEquals(1, $cookie->retrieve('key_one', $string_trafo));
        $this->assertIsInt($cookie->retrieve('key_one', $int_trafo));
    }
}
