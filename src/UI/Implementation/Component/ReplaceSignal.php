<?php

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * Replace Signal
 *
 * @author  Jesús López <lopez@leifos.com>
 */
class ReplaceSignal extends Signal implements \ILIAS\UI\Component\ReplaceSignal
{
    use ComponentHelper;

    /**
     * @inheritdoc
     */
    public function withAsyncRenderUrl($url)
    {
        $this->checkStringArg('url', $url);
        $clone = clone $this;
        $clone->addOption('url', $url);

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getAsyncRenderUrl()
    {
        return (string) $this->getOption('url');
    }
}
