<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component;

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
    public function withAsyncRenderUrl(string $url) : \ILIAS\UI\Component\ReplaceContentSignal
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
