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

use ILIAS\ILIASObject\Properties\Translations\Language;

/**
 * Class ilObjItemGroup
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjItemGroup extends ilObject2
{
    protected ilObjectDefinition $obj_def;
    protected int $access_type;
    protected int $access_begin;
    protected int $access_end;
    protected bool $access_visibility;
    protected ?ilItemGroupAR $item_data_ar = null;

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        $this->log = $DIC["ilLog"];
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();

        $this->tree = $tree;
        $this->obj_def = $objDefinition;
        $this->db = $ilDB;

        $this->item_data_ar = new ilItemGroupAR();

        parent::__construct($a_id, $a_reference);
    }

    public function setId($a_id): void
    {
        parent::setId($a_id);
        $this->item_data_ar->setId($a_id);
    }

    public function initType(): void
    {
        $this->type = "itgr";
    }

    public function getDisplay(): string
    {
        return $this->item_data_ar->getDisplay();
    }

    public function setDisplay(string $a_val): void
    {
        $this->item_data_ar->setDisplay($a_val);
    }

    public function getDisplayWithTitleAndToggleableInitially(): string
    {
        return $this->item_data_ar->getDisplayWithTitleAndToggleableInitially();
    }

    public function setDisplayWithTitleAndToggleableInitially(string $a_val): void
    {
        $this->item_data_ar->setDisplayWithTitleAndToggleableInitially($a_val);
    }

    public function getListPresentation(): string
    {
        return $this->item_data_ar->getListPresentation();
    }

    public function setListPresentation(string $a_val): void
    {
        $this->item_data_ar->setListPresentation($a_val);
    }

    public function getTileSize(): int
    {
        return $this->item_data_ar->getTileSize();
    }

    public function setTileSize(int $a_val): void
    {
        $this->item_data_ar->setTileSize($a_val);
    }

    public function getBehaviour(?bool $stored_value = null): int
    {
        if ($this->getDisplay() !== ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE) {
            return ilItemGroupBehaviour::ALWAYS_OPEN;
        }

        if (is_bool($stored_value)) {
            return $stored_value ? ilItemGroupBehaviour::EXPANDABLE_OPEN : ilItemGroupBehaviour::EXPANDABLE_CLOSED;
        }

        return $this->getDisplayWithTitleAndToggleableInitially() === ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN
            ? ilItemGroupBehaviour::EXPANDABLE_OPEN
            : ilItemGroupBehaviour::EXPANDABLE_CLOSED;
    }

    protected function doRead(): void
    {
        $this->item_data_ar = new ilItemGroupAR($this->getId());
    }

    public function getShowTitle(): bool
    {
        return in_array(
            $this->getDisplay(),
            [ilItemGroupAR::DISPLAY_WITH_TITLE, ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE],
            true
        );
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        global $DIC;

        if ($this->getId()) {
            $this->item_data_ar->setId($this->getId());
            $this->item_data_ar->create();
        }

        $lng = $DIC->language();

        // add default translation
        $obj_trans = $this->getObjectProperties()->getPropertyTranslations();
        $this->getObjectProperties()->storePropertyTranslations(
            $obj_trans->withLanguage(
                new Language(
                    $lng->getDefaultLanguage(),
                    $this->getTitle(),
                    $this->getDescription(),
                    true
                )
            )
        );
    }

    protected function doUpdate(): void
    {
        if ($this->getId()) {
            $this->item_data_ar->update();

            $this->getObjectProperties()->storePropertyTranslations(
                $this->getObjectProperties()->getPropertyTranslations()
                    ->withDefaultTitle($this->getTitle())
                    ->withDefaultDescription($this->getLongDescription())
            );
        }
    }

    protected function doDelete(): void
    {
        if ($this->getId()) {
            $this->item_data_ar->delete();
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjItemGroup);
        $new_obj->setDisplay($this->getDisplay());
        $new_obj->setDisplayWithTitleAndToggleableInitially($this->getDisplayWithTitleAndToggleableInitially());
        $new_obj->setListPresentation($this->getListPresentation());
        $new_obj->setTileSize($this->getTileSize());


        // translations
        $ot = $this->getObjectProperties()->clonePropertyTranslations($new_obj->getId());
        if ($ot->getDefaultTitle() !== "") {
            $new_obj->setTitle($ot->getDefaultTitle());
        }
        if ($ot->getDefaultDescription() !== "") {
            $new_obj->setDescription($ot->getDefaultDescription());
        }

        $new_obj->update();
    }

    public function cloneDependencies(int $a_target_id, int $a_copy_id): bool
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        $ig_items = new ilItemGroupItems($a_target_id);
        $ig_items->cloneItems($this->getRefId(), $a_copy_id);

        return true;
    }

    public static function fixContainerItemGroupRefsAfterCloning(
        ilContainer $a_source_container,
        int $a_copy_id
    ): void {
        global $DIC;

        $ilLog = $DIC["ilLog"];

        $ilLog->write(__METHOD__ . ': Fix item group references in ' . $a_source_container->getType());

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $new_container_ref_id = $mappings[$a_source_container->getRefId()];
        $ilLog->write(__METHOD__ . ': 2-' . $new_container_ref_id . '-');
        $new_container_obj_id = ilObject::_lookupObjId($new_container_ref_id);

        $ilLog->write(__METHOD__ . ': 3' . $new_container_obj_id . '-');
        if (ilPageObject::_exists("cont", $new_container_obj_id)) {
            $ilLog->write(__METHOD__ . ': 4');
            $new_page = new ilContainerPage($new_container_obj_id);
            $new_page->buildDom();
            ilPCResources::modifyItemGroupRefIdsByMapping($new_page, $mappings);
            $new_page->update();
        }
        $ilLog->write(__METHOD__ . ': 5');
    }
}
