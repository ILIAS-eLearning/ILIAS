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

require_once(__DIR__ . '/ModalBase.php');

/**
 * Tests on implementation for the roundtrip modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTripTest extends ModalBase
{
    public function test_get_title(): void
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $this->assertEquals('myTitle', $roundtrip->getTitle());
    }

    public function test_get_content(): void
    {
        $content = $this->getDummyComponent();
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $content);
        $this->assertEquals([$content], $roundtrip->getContent());
        $content = [$this->getDummyComponent(), $this->getDummyComponent()];
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $content);
        $this->assertEquals($content, $roundtrip->getContent());
    }

    public function test_get_action_buttons(): void
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $action_buttons = [
            $this->getButtonFactory()->primary('Action 1', ''),
            $this->getButtonFactory()->standard('Action 2', ''),
        ];
        $roundtrip = $roundtrip->withActionButtons($action_buttons);
        $this->assertEquals($action_buttons, $roundtrip->getActionButtons());
    }

    public function test_with_action_buttons(): void
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $action_buttons = [
            $this->getButtonFactory()->primary('Action 1', ''),
            $this->getButtonFactory()->standard('Action 2', ''),
        ];
        $roundtrip2 = $roundtrip->withActionButtons($action_buttons);
        $this->assertCount(0, $roundtrip->getActionButtons());
        $this->assertCount(2, $roundtrip2->getActionButtons());
        $this->assertEquals($action_buttons, $roundtrip2->getActionButtons());
    }

    public function test_simple_rendering(): void
    {
        $roundtrip = $this->getModalFactory()->roundtrip('Title', $this->getUIFactory()->legacy('Content'))
            ->withActionButtons([
                $this->getButtonFactory()->primary('Action 1', ''),
                $this->getButtonFactory()->standard('Action 2', ''),
            ]);
        $expected = $this->brutallyTrimHTML($this->getExpectedHTML());
        $actual = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($roundtrip));
        $this->assertHTMLEquals($expected, $actual);
    }

    protected function getExpectedHTML(): string
    {
        return <<<EOT
<div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
   <div class="modal-dialog" role="document" data-replace-marker="component">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true"></span></button><span class="modal-title">Title</span>
         </div>
         <div class="modal-body">Content</div>
         <div class="modal-footer">
            <button class="btn btn-default btn-primary" data-action="">Action 1</button>
            <button class="btn btn-default" data-action="">Action 2</button>
            <button class="btn btn-default" data-dismiss="modal">cancel</button>
         </div>
      </div>
   </div>
</div>
EOT;
    }
}
