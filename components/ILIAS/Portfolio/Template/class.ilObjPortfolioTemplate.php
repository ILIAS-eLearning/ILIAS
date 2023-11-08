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

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPortfolioTemplate extends ilObjPortfolioBase
{
    protected bool $activation_limited = false;
    protected bool $activation_visibility = false;
    protected int $activation_starting_time = 0;
    protected int $activation_ending_time = 0;

    protected function initType(): void
    {
        $this->type = "prtt";
    }

    protected function doRead(): void
    {
        parent::doRead();

        if (($this->ref_id ?? 0) > 0) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartDate($activation["timing_start"]);
                    $this->setActivationEndDate($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;

                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        parent::doCreate($clone_mode);
        $this->updateActivation();
    }

    protected function doUpdate(): void
    {
        parent::doUpdate();
        $this->updateActivation();
    }

    protected function deleteAllPages(): void
    {
        // delete pages
        $pages = ilPortfolioTemplatePage::getAllPortfolioPages($this->id);
        foreach ($pages as $page) {
            $page_obj = new ilPortfolioTemplatePage($page["id"]);
            $page_obj->setPortfolioId($this->id);
            $page_obj->delete();
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjPortfolioTemplate);
        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->isOnline());
        }

        self::cloneBasics($this, $new_obj);

        // copy pages
        foreach (ilPortfolioPage::getAllPortfolioPages($this->getId()) as $page) {
            // see ilObjWiki::cloneObject();

            $page = new ilPortfolioTemplatePage($page["id"]);

            $new_page = new ilPortfolioTemplatePage();
            $new_page->setPortfolioId($new_obj->getId());
            $new_page->setTitle($page->getTitle());
            $new_page->setType($page->getType());
            $new_page->setOrderNr($page->getOrderNr());
            $new_page->create(false);

            $page->copy($new_page->getId(), "", $new_obj->getId(), true, $a_copy_id);
        }
    }


    //
    // ACTIVATION
    //

    protected function updateActivation(): void
    {
        // moved activation to ilObjectActivation
        if (($this->ref_id ?? 0) > 0) {
            ilObjectActivation::getItem($this->ref_id);

            $item = new ilObjectActivation();
            if (!$this->isActivationLimited()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStartDate());
                $item->setTimingEnd($this->getActivationEndDate());
                $item->toggleVisible($this->getActivationVisibility());
            }

            $item->update($this->ref_id);
        }
    }

    public function isActivationLimited(): bool
    {
        return $this->activation_limited;
    }

    public function setActivationLimited(bool $a_value): void
    {
        $this->activation_limited = $a_value;
    }

    public function setActivationVisibility(bool $a_value): void
    {
        $this->activation_visibility = $a_value;
    }

    public function getActivationVisibility(): bool
    {
        return $this->activation_visibility;
    }

    public function setActivationStartDate(?int $starting_time = null): void
    {
        $this->activation_starting_time = $starting_time;
    }

    public function setActivationEndDate(?int $ending_time = null): void
    {
        $this->activation_ending_time = $ending_time;
    }

    public function getActivationStartDate(): ?int
    {
        return ($this->activation_starting_time > 0) ? $this->activation_starting_time : null;
    }

    public function getActivationEndDate(): ?int
    {
        return ($this->activation_ending_time > 0) ? $this->activation_ending_time : null;
    }

    //
    // HELPER
    //

    public static function getAvailablePortfolioTemplates(
        string $a_permission = "read"
    ): array {
        global $DIC;

        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();

        $res = array();

        foreach (ilObject::_getObjectsByType("prtt") as $obj) {
            $has_permission = false;

            if ($obj["owner"] == $ilUser->getId()) {
                $has_permission = true;
            } else {
                foreach (ilObject::_getAllReferences($obj["obj_id"]) as $ref_id) {
                    if ($ilAccess->checkAccess($a_permission, "", $ref_id)) {
                        $has_permission = true;
                        break;
                    }
                }
            }

            if ($has_permission) {
                $res[$obj["obj_id"]] = $obj["title"];
            }
        }

        asort($res);
        return $res;
    }
}
