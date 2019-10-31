<?php

require_once(__DIR__ . '/ModalBase.php');

use \ILIAS\UI\Component as C;

/**
 * Tests on abstract base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ModalTest extends ModalBase
{
    public function test_with_close_with_keyboard()
    {
        $modal = $this->getModal();
        $this->assertEquals(true, $modal->getCloseWithKeyboard());
        $modal = $modal->withCloseWithKeyboard(false);
        $this->assertEquals(false, $modal->getCloseWithKeyboard());
    }

    public function test_with_async_rendered_url()
    {
        $modal = $this->getModal()->withAsyncRenderUrl('/fake/async/url');
        $this->assertEquals('/fake/async/url', $modal->getAsyncRenderUrl());
    }

    public function test_get_signals()
    {
        $modal = $this->getModal();
        $show = $modal->getShowSignal();
        $close = $modal->getCloseSignal();
        $this->assertEquals('signal_1', "$show");
        $this->assertEquals('signal_2', "$close");
        $modal2 = $modal->withAsyncRenderUrl('blub');
        $show = $modal2->getShowSignal();
        $close = $modal2->getCloseSignal();
        $this->assertEquals('signal_1', "$show");
        $this->assertEquals('signal_2', "$close");
    }

    public function test_with_reset_signals()
    {
        $modal = $this->getModal();
        $modal2 = $modal->withResetSignals();
        $show = $modal2->getShowSignal();
        $close = $modal2->getCloseSignal();
        $this->assertEquals('signal_3', "$show");
        $this->assertEquals('signal_4', "$close");
    }

    protected function getModal()
    {
        return new ModalMock(new IncrementalSignalGenerator());
    }
}

class ModalMock extends \ILIAS\UI\Implementation\Component\Modal\Modal
{
    public function getCanonicalName()
    {
        return "Modal Mock";
    }
}
