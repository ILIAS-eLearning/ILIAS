<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");

use \ILIAS\UI\Implementation\Component\Input\Container\Form\PostDataFromServerRequest;

use Psr\Http\Message\ServerRequestInterface;

class PostDataFromServerRequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive("getParsedBody")->andReturn(["foo" => "bar"]);
        $this->post_data = new PostDataFromServerRequest($request);
    }


    public function test_get_success()
    {
        $this->assertEquals("bar", $this->post_data->get("foo"));
    }


    public function test_get_fail()
    {
        $raised = false;
        try {
            $this->post_data->get("baz");
        } catch (\LogicException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Logic exception was raised.");
    }


    public function test_getOr_match()
    {
        $this->assertEquals("bar", $this->post_data->getOr("foo", "baz"));
    }


    public function test_getOr_no_match()
    {
        $this->assertEquals("blaw", $this->post_data->getOr("baz", "blaw"));
    }
}
