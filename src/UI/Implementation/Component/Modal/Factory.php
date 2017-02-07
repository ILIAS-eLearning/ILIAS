<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Modal as Modal;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements Modal\Factory {

    /**
     * @inheritdoc
     */
    public function interruptive($title, Component\Component $content)
    {
        return new Interruptive($title, $content);
    }


    /**
     * @inheritdoc
     */
    public function roundtrip($title, Component\Component $content)
    {
        return new RoundTrip($title, $content);
    }


    /**
     * @inheritdoc
     */
    public function lightbox($title, Component\Component $content)
    {
        return new Lightbox($title, $content);
    }
}
