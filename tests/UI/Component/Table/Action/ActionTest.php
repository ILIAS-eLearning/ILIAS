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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Implementation\Component\Table\Action as Implementation;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\URLBuilder;

/**
 * Basic Tests for Table-Actions.
 */
class ActionTest extends ILIAS_UI_TestBase
{
    protected Implementation\Standard $link_action;
    protected Data\URI $link_target;
    protected Implementation\Standard $signal_action;
    protected Signal $signal_target;

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
        $this->link_target = $target;
        $this->link_action = $f->standard($label, $builder, $token);
        $this->signal_target = new Signal('sig_id');
        $this->signal_action = $this->link_action->withAsync(true);
    }

    public function testDataTableActionAttributes(): void
    {
        $act = $this->link_action;
        $this->assertEquals('label', $act->getLabel());
        $this->assertEquals($this->link_target . "&namespace_rowids=", $act->getTarget());
        $this->assertFalse($this->link_action->isAsync());
        $this->assertTrue($this->signal_action->isAsync());
        $this->assertIsString($this->signal_action->getURLBuilderJS());
        $this->assertIsString($this->signal_action->getURLBuilderTokensJS());
    }

    public function testDataTableActionSignalTarget(): void
    {
        $act = $this->signal_action->withSignalTarget($this->signal_target);
        $this->assertEquals($this->signal_target, $act->getTarget());
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
            'ref_id=1&namespace_rowids[]=test-id',
            urldecode($act->getTarget()->getQuery())
        );
    }
}
