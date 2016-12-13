<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Tests on modal implementation
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ModalTest extends ILIAS_UI_TestBase
{

    public function getModalFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Modal\Factory();
    }


    public function getButtonFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Button\Factory();
    }


    public function test_implements_factory_interface()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        $factory = $this->getModalFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\Factory", $factory);

        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');

        $interruptive = $factory->interruptive('myTitle', $content);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\Interruptive", $interruptive);

        $roundTrip = $factory->roundtrip('myTitle', $content);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\RoundTrip", $roundTrip);

        $lightbox = $factory->lightbox('myTitle', $content);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Modal\\LightBox", $lightbox);
    }


    /**
     * @dataProvider modalTypeProvider
     */
    public function test_get_title($modal_type)
    {
        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');
        $factory = $this->getModalFactory();
        $modal = $factory->$modal_type('myTitle', $content);
        $this->assertEquals('myTitle', $modal->getTitle());
    }


    /**
     * @dataProvider modalTypeProvider
     */
    public function test_get_content($modal_type)
    {
        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');
        $factory = $this->getModalFactory();
        $modal = $factory->$modal_type('myTitle', $content);
        $this->assertInstanceOf(\ILIAS\UI\Component\Component::class, $modal->getContent());
    }


    public function test_buttons_count()
    {
        $factory = $this->getModalFactory();
        $button_factory = $this->getButtonFactory();
        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');

        // An interuptive modal has always a cancel button
        $interruptive = $factory->interruptive('myTitle', $content);
        $this->assertEquals(count($interruptive->getButtons()), 1);

        // An interruptive modal can have an action button
        $interruptive = $factory->interruptive('myTitle', $content)
            ->withActionButton($button_factory->primary('Blub', ''));
        $this->assertEquals(count($interruptive->getButtons()), 2);

        // A roundtrip modal has always a cancel button
        $roundTrip = $factory->roundtrip('myTitle', $content);
        $this->assertEquals(count($roundTrip->getButtons()), 1);

        // A roundtrip modal can have multiple buttons
        $roundTrip = $factory->roundtrip('myTitle', $content)
            ->withButtons(array(
                $button_factory->primary('Button 1', ''),
                $button_factory->standard('Button 2', ''),
            ));
        $this->assertEquals(count($roundTrip->getButtons()), 3);

        // A lightbox modal has no buttons
        $lightbox = $factory->lightbox('myTitle', $content);
        $this->assertEquals(count($lightbox->getButtons()), 0);
    }


    /**
     * @dataProvider modalTypeProvider
     */
    public function test_with_title($modal_type)
    {
        $factory = $this->getModalFactory();
        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');

        /** @var C\Modal\Modal $modal */
        $modal = $factory->$modal_type('myTitle', $content);
        $modal2 = $modal->withTitle('myNewTitle');

        $this->assertEquals('myTitle', $modal->getTitle());
        $this->assertEquals('myNewTitle', $modal2->getTitle());
    }


    /**
     * @dataProvider modalTypeProvider
     */
    public function test_with_content($modal_type)
    {
        $factory = $this->getModalFactory();
        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent');
        $content2 = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('myContent2');

        /** @var C\Modal\Modal $modal */
        $modal = $factory->$modal_type('myTitle', $content);
        $modal2 = $modal->withContent($content2);

        $this->assertEquals($content, $modal->getContent());
        $this->assertEquals($content2, $modal2->getContent());
    }


    public function test_cancel_button_on_right()
    {
        $factory = $this->getModalFactory();
        $button_factory = $this->getButtonFactory();
        $renderer = $this->getDefaultRenderer();

        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('MyContent');

        $modal = $factory->interruptive('MyTitle', $content)
            ->withActionButton($button_factory->primary('Action', ''));
        $buttons = $modal->getButtons();
        $cancel_button = array_pop($buttons);
        $this->assertEquals('Cancel', $cancel_button->getLabel());

        $modal = $factory->roundtrip('myTitle', $content)
            ->withButtons(array(
                $button_factory->primary('Button 1', ''),
                $button_factory->standard('Button 2', ''),
                $button_factory->standard('Button 4', ''),
                $button_factory->standard('Button 5', ''),
            ));
        $buttons = $modal->getButtons();
        $cancel_button = array_pop($buttons);
        $this->assertEquals('Cancel', $cancel_button->getLabel());
    }


    public function test_render_interruptive()
    {
        $factory = $this->getModalFactory();
        $button_factory = $this->getButtonFactory();
        $renderer = $this->getDefaultRenderer();

        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('MyContent');
        $modal = $factory->interruptive('MyTitle', $content);

        // Without action button, just cancel button
        $expected_html = $this->getExpectedModalHTML('il-interruptive-modal');
        $rendered_html = $renderer->render($modal);
        $this->assertHTMLEquals($expected_html, $rendered_html);

        // With action button
        $rendered_html = $renderer->render($modal->withActionButton($button_factory->primary('Action', '')));
        $action_button_html = $renderer->render($button_factory->primary('Action', ''));
        $expected_html = $this->getExpectedModalHTML('il-interruptive-modal', '', $action_button_html);
        $this->assertHTMLEquals($expected_html, $rendered_html);
    }


    public function test_render_roundtrip()
    {
        $factory = $this->getModalFactory();
        $button_factory = $this->getButtonFactory();
        $renderer = $this->getDefaultRenderer();

        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('MyContent');
        $modal = $factory->roundtrip('MyTitle', $content);

        // Without action buttons, just cancel button
        $expected_html = $this->getExpectedModalHTML('il-roundtrip-modal');
        $rendered_html = $renderer->render($modal);
        $this->assertHTMLEquals($expected_html, $rendered_html);

        // With action buttons
        $rendered_html = $renderer->render($modal->withButtons(array(
            $button_factory->primary('Primary Action', ''),
            $button_factory->standard('Secondary Action', '')
        )));
        $action_buttons_html = $renderer->render($button_factory->primary('Primary Action', ''));
        $action_buttons_html .= $renderer->render($button_factory->standard('Secondary Action', ''));
        $expected_html = $this->getExpectedModalHTML('il-roundtrip-modal', '', $action_buttons_html);
        $this->assertHTMLEquals($expected_html, $rendered_html);
    }


    public function test_render_lightbox()
    {
        $factory = $this->getModalFactory();
        $renderer = $this->getDefaultRenderer();

        $content = new \ILIAS\UI\Implementation\Component\Legacy\Legacy('MyContent');
        $modal = $factory->lightbox('MyTitle', $content);

        $expected_html = $this->getExpectedModalHTML('il-lightbox-modal');
        $rendered_html = $renderer->render($modal);
        $this->assertHTMLEquals($expected_html, $rendered_html);
    }


    public function modalTypeProvider()
    {
        return array(array("interruptive"), array("roundtrip"), array('lightbox'));
    }


    public function normalizeHTML($html)
    {
        $html = parent::normalizeHTML($html);
        // The times entity is used for closing the modal and not supported in DomDocument::loadXML()
        $html = str_replace('&times;', '', $html);
        preg_match_all('/<a class="btn btn-\w+".*id="(.*)"/', $html, $matches);
        $button_ids = $matches[1];
        $replaces = array();
        foreach ($matches as $match) {
            $replaces[] = 'FAKE_BUTTON_ID';
        }

        return str_replace($button_ids, $replaces, $html);
    }


    private function getExpectedModalHTML($css_class, $modal_id = '', $modal_buttons = '')
    {
        $expected = <<<EOT
<div class="modal fade [[CSS_CLASS]]" tabindex="-1" role="dialog"[[MODAL_ID]]>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">MyTitle</h4>
            </div>
            <div class="modal-body">
                MyContent
            </div>
EOT;
        if (in_array($css_class, array('il-interruptive-modal', 'il-roundtrip-modal'))) {
            $expected .= <<<EOT
            <div class="modal-footer">
                [[MODAL_BUTTONS]]
                <a class="btn btn-default" href="" data-action="" id="FAKE_BUTTON_ID">Cancel</a>
            </div>
EOT;
        }
        $expected .= "</div></div></div>";

        $searches = array('[[CSS_CLASS]]', '[[MODAL_ID]]', '[[MODAL_BUTTONS]]');
        $replaces = array($css_class, $modal_id, $modal_buttons);

        return str_replace($searches, $replaces, $expected);
    }

}
