<?php

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * Class ReplaceContentSignal
 *
 * Dev note: This class is copied from the popover. TODO-> DRY and centralize it.
 *
 * @author  Jesús López <lopez@leifos.com>
 */
class ReplaceContentSignal extends Signal implements \ILIAS\UI\Component\ReplaceContentSignal
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
