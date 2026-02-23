<?php

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

declare(strict_types=1);

namespace ILIAS\GlobalScreen\GUI\Tabs;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Structure
{
    /**
     * @var Tab[]
     */
    private array $tabs = [];

    public function add(Tab $tab): void
    {
        $this->tabs[$tab->getId()] = $tab;
    }

    /**
     * @return Tab[]
     */
    public function get(): array
    {
        return $this->tabs;
    }

    public function getById(string $id): ?Tab
    {
        return $this->tabs[$id] ?? null;
    }

    public function getByHandlingClass(string $handling_class): ?Tab
    {
        $handling_class = strtolower($handling_class);
        foreach ($this->tabs as $tab) {
            if (strtolower($tab->getHandlingClass()) === strtolower($handling_class)) {
                return $tab;
            }
        }
        return null;
    }

}
