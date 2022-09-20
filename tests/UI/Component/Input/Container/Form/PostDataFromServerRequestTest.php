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

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Component\Input\Container\Form\PostDataFromServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class PostDataFromServerRequestTest extends TestCase
{
    protected PostDataFromServerRequest $post_data;

    public function setUp(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive("getParsedBody")->andReturn(["foo" => "bar"]);
        $this->post_data = new PostDataFromServerRequest($request);
    }

    public function test_get_success(): void
    {
        $this->assertEquals("bar", $this->post_data->get("foo"));
    }

    public function test_get_fail(): void
    {
        $raised = false;
        try {
            $this->post_data->get("baz");
        } catch (LogicException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Logic exception was raised.");
    }

    public function test_getOr_match(): void
    {
        $this->assertEquals("bar", $this->post_data->getOr("foo", "baz"));
    }

    public function test_getOr_no_match(): void
    {
        $this->assertEquals("blaw", $this->post_data->getOr("baz", "blaw"));
    }
}
