<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component;

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
    public function withAsyncRenderUrl(string $url) : ReplaceSignal
    {
        $clone = clone $this;
        $clone->addOption('url', $url);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAsyncRenderUrl() : string
    {
        return (string) $this->getOption('url');
    }
}
