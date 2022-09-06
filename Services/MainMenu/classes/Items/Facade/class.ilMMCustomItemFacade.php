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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;

/**
 * Class ilMMCustomItemFacade
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomItemFacade extends ilMMAbstractItemFacade
{
    protected ?ilMMCustomItemStorage $custom_item_storage;

    protected string $action = '';

    protected string $type = '';

    protected bool $top_item = false;

    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $identification, Main $collector)
    {
        parent::__construct($identification, $collector);
        $this->custom_item_storage = $this->getCustomStorage();
        if ($this->custom_item_storage instanceof ilMMCustomItemStorage) {
            if ($this->custom_item_storage->getType()) {
                $this->type = $this->custom_item_storage->getType();
            }
            $this->role_based_visibility = $this->custom_item_storage->hasRoleBasedVisibility();
            if ($this->custom_item_storage->hasRoleBasedVisibility()) {
                $this->global_role_ids = $this->custom_item_storage->getGlobalRoleIDs();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function update(): void
    {
        if ($this->isCustom()) {
            $mm = $this->getCustomStorage();
            if ($mm instanceof ilMMCustomItemStorage) {
                if ($this->default_title === '-') {
                    $this->default_title = ilMMItemTranslationStorage::getDefaultTranslation($this->identification());
                }
                $mm->setDefaultTitle($this->default_title);
                $mm->setType($this->getType());
                $mm->setRoleBasedVisibility($this->role_based_visibility);
                if ($this->role_based_visibility) {
                    $mm->setGlobalRoleIDs($this->global_role_ids);
                }
                $mm->update();
            }
        }
        parent::update();
    }

    /**
     * @inheritDoc
     */
    public function delete(): void
    {
        if (!$this->isDeletable()) {
            throw new LogicException("Non Custom items can't be deleted");
        }

        $cm = $this->getCustomStorage();
        if ($cm instanceof ilMMCustomItemStorage) {
            $cm->delete();
        }
        parent::delete();
    }

    private function getCustomStorage(): ?ilMMCustomItemStorage
    {
        $id = $this->raw_item->getProviderIdentification()->getInternalIdentifier();
        /** @var ilMMCustomItemStorage $mm */
        $mm = ilMMCustomItemStorage::find($id);

        return $mm;
    }

    /**
     * @inheritDoc
     */
    public function supportsRoleBasedVisibility(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isCustom(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEditable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isDeletable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation(): string
    {
        return "Custom";
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function isTopItem(): bool
    {
        if ($this->raw_item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem) {
            return parent::isTopItem();
        }

        return $this->top_item;
    }

    /**
     * @inheritDoc
     */
    public function setIsTopItm(bool $top_item): void
    {
        $this->top_item = $top_item;
    }
}
