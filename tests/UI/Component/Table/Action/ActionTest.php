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

/**
 * Basic Tests for Table-Actions.
 */
class ActionTest extends ILIAS_UI_TestBase
{
    protected function buildFactories()
    {
        return [
            new Implementation\Factory(),
            new Data\Factory()
        ];
    }

    public function testAttributes(): Implementation\Standard
    {
        list($f, $df) = $this->buildFactories();
        $label = 'label';
        $param = 'param';
        $target = $df->uri('http://wwww.ilias.de?ref_id=1');
        $act = $f->standard($label, $param, $target);

        $this->assertEquals($label, $act->getLabel());
        $this->assertEquals($label, $act->getLabel());
        $this->assertEquals($target, $act->getTarget());
        return $act;
    }

    public function testSignalTarget(): Implementation\Standard
    {
        list($f, $df) = $this->buildFactories();
        $label = 'label2';
        $param = 'param2';
        $target = new Signal('sig-id');
        $act = $f->standard($label, $param, $target);
        $this->assertEquals($target, $act->getTarget());
        return $act;
    }

    public function testStringTarget()
    {
        $this->expectException(\TypeError::class);
        list($f, $df) = $this->buildFactories();
        $act = $f->standard('', '', '');
    }

    /**
     * @depends testAttributes
     */
    public function testRowIdOnURI(Implementation\Standard $act)
    {
        $act = $act->withRowId('test-id');
        $this->assertEquals(
            'ref_id=1&param=test-id',
            $act->getTarget()->getQuery()
        );
    }

    /**
     * @depends testSignalTarget
     */
    public function testRowIdOnSignal(Implementation\Standard $act)
    {
        $act = $act->withRowId('test-id2');
        $this->assertEquals(
            'test-id2',
            $act->getTarget()->getOptions()['param2']
        );
    }
}
