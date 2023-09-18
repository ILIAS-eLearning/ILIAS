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
 */

declare(strict_types=1);

use ILIAS\UI\Implementation\Component\Modal\Interruptive;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class ilObjFileComponentBuilder
{
    public function __construct(
        protected ilLanguage $lng,
        protected \ILIAS\DI\UIServices $ui
    ) {
    }

    public function buildConfirmDeleteSpecificVersionsModal(
        string $action,
        ilObjFile $file,
        array $version_ids
    ): Interruptive {
        $icon = $this->ui->factory()->image()->standard(
            ilObject::_getIcon($file->getId(), "small", $file->getType()),
            $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $file->getType())
        );

        $modal_items = [];
        foreach ($file->getVersions($version_ids) as $version) {
            $filename = $version['filename'] ?? $version->getFilename() ?? $file->getTitle();
            $version_nr = $version['hist_id'] ?? $version->getVersion();
            $item_title = $filename . " (v" . $version_nr . ")";
            $modal_items[] = $this->ui->factory()->modal()->interruptiveItem()->standard(
                (string) $version['hist_entry_id'],
                $item_title,
                $icon
            );
        }

        return $this->ui->factory()->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('file_confirm_delete_versions'),
            $action
        )->withAffectedItems(
            $modal_items
        );
    }

    public function buildConfirmDeleteAllVersionsModal(
        string $action,
        ilObjFile $file,
    ): Interruptive {
        $icon = $this->ui->factory()->image()->standard(
            ilObject::_getIcon($file->getId(), "small", $file->getType()),
            $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $file->getType())
        );

        return $this->ui->factory()->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('file_confirm_delete_all_versions'),
            $action
        )->withAffectedItems(
            [
                $this->ui->factory()->modal()->interruptiveItem()->standard(
                    (string) $file->getRefId(),
                    $file->getTitle(),
                    $icon
                )
            ]
        );
    }
}
