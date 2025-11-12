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

use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticFooterProvider;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Permanent;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ilFooterCustomGroupsProvider extends AbstractStaticFooterProvider
{
    private readonly GroupsRepositoryDB $groups_repository;
    private readonly EntriesRepositoryDB $entries_repository;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->groups_repository = new GroupsRepositoryDB($dic->database(), $this);
        $this->entries_repository = new EntriesRepositoryDB($dic->database(), $this);
    }

    public function getNewIdentification(): IdentificationInterface
    {
        return $this->id_factory->identifier(uniqid('', true));
    }

    public function getGroupsRepository(): GroupsRepositoryDB
    {
        return $this->groups_repository;
    }

    public function getEntriesRepository(): EntriesRepositoryDB
    {
        return $this->entries_repository;
    }



    public function getGroups(): array
    {
        $groups = [];

        foreach ($this->groups_repository->all() as $group) {
            if ($group->isCore()) {
                continue;
            }
            if (!$group->isActive()) {
                continue;
            }
            $groups[] = $this->item_factory->group(
                $this->dic->globalScreen()->identification()->fromSerializedIdentification($group->getId()),
                $group->getTitle(),
            );
        }

        return $groups;
    }

    public function getEntries(): array
    {
        $entries = [];

        foreach ($this->entries_repository->all() as $entry) {
            if ($entry->isCore()) {
                continue;
            }
            if (!$entry->isActive()) {
                continue;
            }
            try {
                $action = new URI($entry->getAction());
            } catch (Throwable) {
                continue;
            }

            $entries[] = $this->item_factory->link(
                $this->dic->globalScreen()->identification()->fromSerializedIdentification($entry->getId()),
                $entry->getTitle(),
            )->withParent(
                $this->dic->globalScreen()->identification()->fromSerializedIdentification($entry->getParent())
            )->withAction(
                $action
            )->withOpenInNewViewport($entry->isExternal());
        }

        return $entries;
    }

    private function buildURI(string $from_path): URI
    {
        $request = $this->dic->http()->request()->getUri();
        return new URI($request->getScheme() . '://' . $request->getHost() . '/' . ltrim($from_path, '/'));
    }

    #[\Override]
    public function getAdditionalTexts(): array
    {
        return [];
    }

    public function getPermanentURI(): ?Permanent
    {
        return null;
    }

    private function txt(string $key): string
    {
        return $this->dic->language()->txt($key);
    }

}
