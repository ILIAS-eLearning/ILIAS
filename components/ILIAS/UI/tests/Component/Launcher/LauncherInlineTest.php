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

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data\URI;
use ILIAS\Refinery\Factory as Refinery;

class LauncherInlineTest extends ILIAS_UI_TestBase
{
    protected ILIAS\Data\Factory $df;
    protected ilLanguage $language;

    public function setUp(): void
    {
        $this->df = new \ILIAS\Data\Factory();
    }

    protected function getInputFactory(): I\Input\Field\Factory
    {
        $this->language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(I\Input\UploadLimitResolver::class),
            new I\SignalGenerator(),
            $this->df,
            new Refinery($this->df, $this->language),
            $this->language
        );
    }

    protected function getModalFactory(): I\Modal\Factory
    {
        return new I\Modal\Factory(
            new I\SignalGenerator(),
            new I\Modal\InterruptiveItem\Factory(),
            $this->getInputFactory()
        );
    }

    protected function getIconFactory(): I\Symbol\Icon\Factory
    {
        return new I\Symbol\Icon\Factory();
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public I\SignalGenerator $sig_gen;
            public I\Input\Field\Factory $input_factory;

            public function button(): C\Button\Factory
            {
                return new I\Button\Factory(
                    $this->sig_gen
                );
            }
            public function symbol(): C\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function modal(): C\Modal\Factory
            {
                return new I\Modal\Factory(
                    $this->sig_gen,
                    new I\Modal\InterruptiveItem\Factory(),
                    $this->input_factory
                );
            }
        };
        $factory->sig_gen = new I\SignalGenerator();
        $factory->input_factory = $this->getInputFactory();
        return $factory;
    }

    protected function getURI(): URI
    {
        return $this->df->uri('http://localhost/ilias.php');
    }

    protected function getLauncher(): I\Launcher\Inline
    {
        $target = $this->df->link('LaunchSomething', $this->getURI());
        return new I\Launcher\Inline(
            $this->getModalFactory(),
            $target
        );
    }
    protected function getMessageBox(): I\MessageBox\MessageBox
    {
        return new I\MessageBox\MessageBox(C\MessageBox\MessageBox::INFO, 'message');
    }

    public function testLauncherInlineConstruction(): void
    {
        $l = $this->getLauncher();
        $this->assertInstanceOf(C\Launcher\Inline::class, $l);
        $this->assertEquals($this->df->link('LaunchSomething', $this->getURI()), $l->getTarget());
        $this->assertEquals('LaunchSomething', $l->getButtonLabel());
        $this->assertTrue($l->isLaunchable());
        $this->assertNull($l->getStatusIcon());
        $this->assertNull($l->getStatusMessageBox());
        $this->assertNull($l->getModal());
        $this->assertNull($l->getModalSubmitLabel());
        $this->assertNull($l->getModalCancelLabel());
    }

    public function testLauncherInlineBasicModifier(): void
    {
        $msg = $this->getMessageBox();
        $icon = $this->getIconFactory()->standard('course', 'some icon');
        $some_submit_label = 'some submit label';
        $some_cancel_label = 'some cancel label';
        $l = $this->getLauncher()
            ->withDescription('some description')
            ->withButtonLabel('different label', false)
            ->withStatusMessageBox($msg)
            ->withStatusIcon($icon)
            ->withModalSubmitLabel($some_submit_label)
            ->withModalCancelLabel($some_cancel_label)
        ;

        $this->assertEquals($this->df->link('LaunchSomething', $this->getURI()), $l->getTarget());
        $this->assertEquals('different label', $l->getButtonLabel());
        $this->assertfalse($l->isLaunchable());
        $this->assertEquals($msg, $l->getStatusMessageBox());
        $this->assertEquals($icon, $l->getStatusIcon());
        $this->assertNull($l->getModal());
        $this->assertEquals($l->getModalSubmitLabel(), $some_submit_label);
        $this->assertEquals($l->getModalCancelLabel(), $some_cancel_label);
    }

    public function testLauncherInlineWithFields(): void
    {
        $ff = $this->getInputFactory();
        $field = $ff->checkbox('Understood', 'ok');
        $group = $ff->group([$field]);
        $evaluation = fn(Result $result, Launcher & $launcher) => true;
        $instruction = $this->getMessageBox();
        $l = $this->getLauncher()
            ->withInputs($group, $evaluation, $instruction);

        $this->assertEquals($evaluation, $l->getEvaluation());
        $this->assertInstanceOf(C\Modal\Roundtrip::class, $l->getModal());

        $this->assertEquals(
            $instruction,
            $l->getModal()->getContent()[0]
        );

        $ns = new class () extends I\Input\FormInputNameSource {
            public function getNewName(): string
            {
                return 'form/input_0';
            }
        };
        $this->assertEquals(
            [$field->withNameFrom($ns)],
            $l->getModal()->getInputs()
        );
    }

    public function testLauncherInlineRendering(): void
    {
        $ff = $this->getInputFactory();
        $group = $ff->group([$ff->checkbox('Understood', 'ok')]);
        $evaluation = fn(Result $result, Launcher & $launcher) => true;
        $msg = $this->getMessageBox();
        $icon = $this->getIconFactory()->standard('course', 'some icon');

        $l = $this->getLauncher()
            ->withDescription('some description')
            ->withButtonLabel('different label', false)
            ->withStatusMessageBox($msg)
            ->withStatusIcon($icon)
            ->withInputs($group, $evaluation, $msg)
            ->withModalSubmitLabel('some submit label')
            ->withModalCancelLabel('some cancel label')
        ;

        $expected = <<<EXP
<div class="c-launcher c-launcher--inline" id="">
    <div class="c-launcher__status">
        <div class="c-launcher__status__message">
            <div class="alert alert-info" role="status">
                <div class="ilAccHeadingHidden"><a id="il_message_focus" name="il_message_focus">info_message</a></div>message</div>
        </div>
        <div class="c-launcher__status__icon"><img class="icon course small" src="./templates/default/images/standard/icon_default.svg" alt="some icon"/></div>
    </div>
    <div class="c-launcher__description">
        some description
    </div>
    <button class="btn btn-bulky" id="id_5" disabled="disabled"><span class="glyph" role="img"><span class="glyphicon glyphicon-launch" aria-hidden="true"></span></span><span class="bulky-label">different label</span></button>
    <div class="c-launcher__form">
        <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
            <div class="modal-dialog" role="document" data-replace-marker="component">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                        <h1 class="modal-title">different label</h1>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" role="status">
                            <div class="ilAccHeadingHidden"><a id="il_message_focus" name="il_message_focus">info_message</a></div>message</div>
                        <form id="id_3" role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="http://localhost/ilias.php" method="post" novalidate="novalidate">
                            <div class="form-group row">
                                <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">Understood</label>
                                <div class="col-sm-8 col-md-9 col-lg-10">
                                    <input type="checkbox" id="id_2" value="checked" name="form/input_0" class="form-control form-control-sm" />
                                    <div class="help-block">ok</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" id="id_4">some submit label</button>
                        <button class="btn btn-default" data-dismiss="modal">some cancel label</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EXP;
        $r = $this->getDefaultRenderer();
        $actual = $r->render($l);
        $this->assertEquals(
            $this->brutallyTrimSignals($this->brutallyTrimHTML($expected)),
            $this->brutallyTrimSignals($this->brutallyTrimHTML($actual))
        );
    }
}
