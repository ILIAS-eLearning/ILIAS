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

namespace ILIAS\Container\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ModeManager
{
    protected bool $ordering_mode = false;
    protected \ILIAS\Repository\Clipboard\ClipboardManager $clipboard;
    protected ModeSessionRepository $mode_repo;
    protected \ilContainer $container;

    public function __construct(
        \ilContainer $container,
        ModeSessionRepository $mode_repo,
        \ILIAS\Repository\Clipboard\ClipboardManager $clipboard
    ) {
        $this->container = $container;
        $this->mode_repo = $mode_repo;
        $this->clipboard = $clipboard;
    }

    public function setAdminMode(): void
    {
        $this->mode_repo->setAdminMode();
    }

    public function setContentMode(): void
    {
        $this->mode_repo->setContentMode();
    }

    public function setOrderingMode(): void
    {
        $this->mode_repo->setContentMode();
        $this->ordering_mode = true;
    }

    public function isAdminMode(): bool
    {
        return $this->mode_repo->isAdminMode();
    }

    public function isContentMode(): bool
    {
        return $this->mode_repo->isContentMode();
    }

    public function isOrderingMode(): bool
    {
        return $this->ordering_mode;
    }

    public function showAdminCheckboxes(): bool
    {
        return ($this->isAdminMode() && !$this->clipboard->hasEntries());
    }

    public function isActiveItemOrdering(): bool
    {
        if ($this->isOrderingMode()) {
            if ($this->container->getViewMode() == \ilContainer::VIEW_OBJECTIVE) {
                return false;
            }
            return (\ilContainerSortingSettings::_lookupSortMode($this->container->getId()) === \ilContainer::SORT_MANUAL);
        }
        return false;
    }
}
