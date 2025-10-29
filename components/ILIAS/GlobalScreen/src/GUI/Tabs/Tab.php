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

use ILIAS\Data\URI;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Tab
{
    private string $permission = 'read';

    public function __construct(
        private string $id,
        private string $language_key,
        private URI $target,
        private string $handling_class,
        private ?Tab $parent = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHandlingClass(): string
    {
        return $this->handling_class;
    }

    public function getLanguageKey(): string
    {
        return $this->language_key;
    }

    public function getTarget(): URI
    {
        return $this->target;
    }

    public function getParent(): ?Tab
    {
        return $this->parent;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function withPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function withParent(Tab $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

}
