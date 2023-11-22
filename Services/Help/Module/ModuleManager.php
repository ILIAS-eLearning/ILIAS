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

namespace ILIAS\Help\Module;

use ILIAS\Help\InternalRepoService;
use ILIAS\Help\InternalDomainService;

class ModuleManager
{
    protected \ilObjUser $user;
    protected \ILIAS\Help\Tooltips\TooltipsManager $tooltips;
    protected \ILIAS\Help\Map\MapManager $help_map;
    protected \ilAppEventHandler $event;
    protected ModuleDBRepository $repo;
    protected \ilSetting $settings;

    public function __construct(
        ModuleDBRepository $repo,
        InternalDomainService $domain
    ) {
        $this->repo = $repo;
        $this->settings = $domain->settings();
        $this->help_map = $domain->map();
        $this->tooltips = $domain->tooltips();
        $this->event = $domain->event();
        $this->user = $domain->user();
    }

    public function upload(
        array $file
    ): void {
        $id = $this->repo->create();

        try {
            $imp = new \ilImport();
            $conf = $imp->getConfig("Services/Help");
            $conf->setModuleId($id);
            $new_id = $imp->importObject(null, $file["tmp_name"], $file["name"], "lm", "Modules/LearningModule"); //
            $newObj = new \ilObjLearningModule($new_id, false);

            $this->repo->writeHelpModuleLmId($id, $newObj->getId());
        } catch (\ilManifestFileNotFoundImportException $e) {
            throw new \ilLMOldExportFileException("This file seems to be from ILIAS version 5.0.x or lower. Import is not supported anymore.");
        }

        $this->event->raise(
            'Services/Help',
            'create',
            array(
                'obj_id' => $id,
                'obj_type' => 'lm'
            )
        );
    }
    public function getHelpModules(): array
    {
        return $this->repo->getHelpModules();
    }

    /**
     * @return int[]
     */
    public function getActiveModules(): array
    {
        $ids = [];
        foreach ($this->getHelpModules() as $m) {
            if ($m["active"]) {
                $ids[] = (int) $m["id"];
            }
        }
        return $ids;
    }

    public function isHelpActive(): bool
    {
        if ($this->user->getLanguage() !== "de") {
            return false;
        }
        if ($this->isAuthoringMode()) {
            return true;
        }
        return (count($this->getActiveModules()) > 0);
    }

    public function isAuthoringMode(): bool
    {
        return ($this->getAuthoringLMId() > 0);
    }
    public function getAuthoringLMId(): int
    {
        $lm_id = 0;
        if (defined('OH_REF_ID') && (int) OH_REF_ID > 0) {
            $lm_id = \ilObject::_lookupObjId((int) OH_REF_ID);
        }
        return $lm_id;
    }

    public function lookupModuleTitle(
        int $id
    ): string {
        return $this->repo->lookupModuleTitle($id);
    }

    public function lookupModuleLmId(
        int $id
    ): int {
        return $this->repo->lookupModuleLmId($id);
    }

    public function activate(int $module_id): void
    {
        $this->repo->writeActive($module_id, true);
    }

    public function deactivate(int $module_id): void
    {
        $this->repo->writeActive($module_id, false);
    }

    public function deleteModule(
        int $id
    ): void {
        $lm_id = $this->repo->lookupModuleLmId($id);

        // delete learning module
        if (\ilObject::_lookupType((int) $rec["lm_id"]) === "lm") {
            $lm = new \ilObjLearningModule((int) $rec["lm_id"], false);
            $lm->delete();
        }

        // delete mappings
        $this->help_map->deleteEntriesOfModule($id);

        // delete tooltips
        $this->tooltips->deleteTooltipsOfModule($id);

        $this->repo->deleteModule($id);
    }

    public function isHelpLM(
        int $lm_id
    ): bool {
        return $this->repo->isHelpLM($lm_id);
    }

    public function saveOrder(array $order): void
    {
        $this->repo->saveOrder($order);
    }
}
