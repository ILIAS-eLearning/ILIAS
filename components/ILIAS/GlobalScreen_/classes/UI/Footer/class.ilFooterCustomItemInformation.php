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

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;
use ILIAS\GlobalScreen\Scope\Footer\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Footer\Factory\hasTitle;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepository;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepository;
use ILIAS\GlobalScreen\Scope\Footer\Factory\canHaveParent;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isGroup;
use ILIAS\GlobalScreen\UI\Footer\Groups\Group;
use ILIAS\GlobalScreen\UI\Footer\Entries\Entry;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepository;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ilFooterCustomItemInformation implements ItemInformation
{
    private ?GroupsRepositoryDB $groups_repository = null;
    private ?EntriesRepositoryDB $entries_repository = null;
    private ?IdentificationFactory $identifications = null;
    private ?TranslationsRepositoryDB $translations_repository = null;
    private ?string $user_language = null;

    public function __construct(private readonly Container $dic)
    {
    }

    private function translations(): TranslationsRepository
    {
        if ($this->translations_repository !== null) {
            return $this->translations_repository;
        }

        $this->translations_repository = new TranslationsRepositoryDB($this->dic->database());
        return $this->translations_repository;
    }

    private function userLanguage(): string
    {
        if ($this->user_language !== null) {
            return $this->user_language;
        }
        return $this->user_language = $this->dic->user()->getLanguage();
    }

    private function groups(): GroupsRepository
    {
        if ($this->groups_repository !== null) {
            return $this->groups_repository;
        }

        $this->groups_repository = new GroupsRepositoryDB($this->dic->database());
        $this->groups_repository->preload();
        return $this->groups_repository;
    }

    private function entries(): EntriesRepository
    {
        if ($this->entries_repository !== null) {
            return $this->entries_repository;
        }

        $this->entries_repository = new EntriesRepositoryDB($this->dic->database());
        $this->entries_repository->preload();
        return $this->entries_repository;
    }

    private function id(): IdentificationFactory
    {
        return $this->identifications ?? $this->dic->globalScreen()->identification();
    }

    private function maybeGetItem(isItem $item): Group|Entry|null
    {
        if ($item instanceof canHaveParent) {
            return $this->entries()->get($item->getProviderIdentification()->serialize());
        }

        if ($item instanceof isGroup) {
            return $this->groups()->get($item->getProviderIdentification()->serialize());
        }
        return null;
    }

    public function isItemActive(isItem $item): bool
    {
        $d = $this->maybeGetItem($item);
        if ($d === null) {
            return $item->isAvailable();
        }

        return $d->isActive();
    }

    public function customPosition(isItem $item): isItem
    {
        $d = $this->maybeGetItem($item);
        if ($d === null) {
            return $item;
        }

        return $item->withPosition($d->getPosition());
    }

    public function customTranslationForUser(hasTitle $item): hasTitle
    {
        $d = $this->maybeGetItem($item);
        if ($d === null) {
            return $item;
        }

        if (
            (($translation = $this->translations()->get($d)->getLanguageCode($this->userLanguage())) !== null)
            && $translation->getTranslation() !== ''
        ) {
            return $item->withTitle($translation->getTranslation());
        }

        return $item->withTitle($d->getTitle());
    }

    public function getParent(isItem $item): IdentificationInterface
    {
        $entry = $this->entries()->get($item->getProviderIdentification()->serialize());

        if ($entry === null) {
            return $item->getProviderIdentification();
        }

        return $this->id()->fromSerializedIdentification($entry->getParent());
    }

}
