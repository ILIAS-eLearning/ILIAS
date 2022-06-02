<?php declare(strict_types=1);

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
