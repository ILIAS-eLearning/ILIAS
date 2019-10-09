<?php

require_once(__DIR__ . '/ModalBase.php');

/**
 * Tests on implementation for the roundtrip modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTripTest extends ModalBase
{
    public function test_get_title()
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $this->assertEquals('myTitle', $roundtrip->getTitle());
    }

    public function test_get_content()
    {
        $content = $this->getDummyComponent();
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $content);
        $this->assertEquals([$content], $roundtrip->getContent());
        $content = [$this->getDummyComponent(), $this->getDummyComponent()];
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $content);
        $this->assertEquals($content, $roundtrip->getContent());
    }

    public function test_get_action_buttons()
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $action_buttons = [
            $this->getButtonFactory()->primary('Action 1', ''),
            $this->getButtonFactory()->standard('Action 2', ''),
        ];
        $roundtrip = $roundtrip->withActionButtons($action_buttons);
        $this->assertEquals($action_buttons, $roundtrip->getActionButtons());
    }

    public function test_with_action_buttons()
    {
        $roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
        $action_buttons = [
            $this->getButtonFactory()->primary('Action 1', ''),
            $this->getButtonFactory()->standard('Action 2', ''),
        ];
        $roundtrip2 = $roundtrip->withActionButtons($action_buttons);
        $this->assertEquals(0, count($roundtrip->getActionButtons()));
        $this->assertEquals(2, count($roundtrip2->getActionButtons()));
        $this->assertEquals($action_buttons, $roundtrip2->getActionButtons());
    }


    public function test_simple_rendering()
    {
        $roundtrip = $this->getModalFactory()->roundtrip('Title', $this->getUIFactory()->legacy('Content'))
            ->withActionButtons([
                $this->getButtonFactory()->primary('Action 1', ''),
                $this->getButtonFactory()->standard('Action 2', ''),
            ]);
        $expected = $this->normalizeHTML($this->getExpectedHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($roundtrip));
        $this->assertHTMLEquals($expected, $actual);
    }


    protected function getExpectedHTML()
    {
        $expected = <<<EOT
<div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1"><div class="modal-dialog" role="document" data-replace-marker="component"><div class="modal-content">
 <div class="modal-header">
 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button><h4 class="modal-title">Title</h4>
 </div>
 <div class="modal-body">Content</div>
 <div class="modal-footer">
 <button class="btn btn-default btn-primary" data-action="">Action 1</button><button class="btn btn-default" data-action="">Action 2</button><a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a>
 </div>
 </div></div></div>
EOT;
        return $expected;
    }
}
