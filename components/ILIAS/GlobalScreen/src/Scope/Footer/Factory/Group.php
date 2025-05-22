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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Group extends AbstractBaseItem implements isGroup
{
    use hasTitleTrait;

    protected array $entries = [];

    public function __construct(IdentificationInterface $provider_identification, string $title)
    {
        parent::__construct($provider_identification);
        $this->title = $title;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function withEntries(array $entries): isGroup
    {
        return $this->addEntries(...$entries);
    }

    public function addEntries(canHaveParent ...$entries): isGroup
    {
        foreach ($entries as $entry) {
            if (isset($this->entries[$entry->getProviderIdentification()->serialize()])) {
                continue;
            }
            $this->entries[$entry->getProviderIdentification()->serialize()] = $entry;
        }

        return $this;
    }

    public function addEntry(canHaveParent $entry): isGroup
    {
        return $this->addEntries($entry);
    }

    public function isTop(): bool
    {
        return true;
    }

}
