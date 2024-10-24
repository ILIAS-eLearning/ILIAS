<?php

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

declare(strict_types=1);

require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Implementation\Component\Table\Action as Implementation;
use ILIAS\Data;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Implementation\Component\Prompt\Standard as Prompt;

/**
 * Basic Tests for Table-Actions.
 */
class ActionTest extends ILIAS_UI_TestBase
{
    protected Implementation\Standard $link_action;
    protected Data\URI $link_target;
    protected Implementation\Standard $prompt_action;
    protected Implementation\Standard $async_action;

    protected function buildFactories()
    {
        return [
            new Implementation\Factory(),
            new Data\Factory()
        ];
    }

    public function setUp(): void
    {
        list($f, $df) = $this->buildFactories();
        $target = $df->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($target);
        list($builder, $token) = array_values(
            $url_builder->acquireParameter(['namespace'], 'rowids')
        );
        $label = 'label';
        $prompt = $this->createMock(Prompt::class);

        $this->link_target = $target;
        $this->link_action = $f->standard($label, $builder, $token);
        $this->prompt_action = $f->standard($label, $prompt, $token);
        $this->async_action = $this->link_action->withAsync();
    }

    public function testDataTableActionAttributes(): void
    {
        $act = $this->link_action;
        $this->assertEquals('label', $act->getLabel());
        $this->assertEquals($this->link_target . "&namespace_rowids=", $act->getTarget()->buildURI());
        $this->assertIsString($this->link_action->getURLBuilderJS());
        $this->assertIsString($this->link_action->getURLBuilderTokensJS());
    }

    public function testDataTableActionStringTarget(): void
    {
        $this->expectException(\TypeError::class);
        list($f, $df) = $this->buildFactories();
        $act = $f->standard('', '', '');
    }

    public function testDataTableActionRowIdOnURI(): void
    {
        $act = $this->link_action->withRowId('test-id');
        $this->assertEquals(
            'http://wwww.ilias.de?ref_id=1&namespace_rowids[]=test-id',
            urldecode($act->getTarget()->buildURI()->__toString())
        );
    }

    public function testDataTableActionModes(): void
    {
        $this->assertFalse($this->link_action->isPrompt());
        $this->assertFalse($this->async_action->isPrompt());
        $this->assertTrue($this->prompt_action->isPrompt());
        $this->assertFalse($this->prompt_action->isAsync());
        $this->assertTrue($this->async_action->isAsync());
    }

    public function testDataTableActionPromptMayNotBeAsync(): void
    {
        $this->expectException(\LogicException::class);
        $this->prompt_action->withAsync(true);
    }
}
