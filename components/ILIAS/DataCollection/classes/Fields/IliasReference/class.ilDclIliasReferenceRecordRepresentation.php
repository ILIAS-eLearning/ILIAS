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

use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Services;

class ilDclIliasReferenceRecordRepresentation extends ilDclBaseRecordRepresentation
{
    private ilObjectDefinition $obj_def;
    private Services $static_url;

    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        parent::__construct($record_field);
        global $DIC;

        $this->obj_def = $DIC['objDefinition'];
        $this->static_url = $DIC['static_url'];
    }

    public function getHTML(bool $link = true, array $options = []): string
    {
        $ref_id = (int) $this->getRecordField()->getValue();
        if ($ref_id === 0) {
            return '';
        }

        $object = ilObjectFactory::getInstanceByRefId($ref_id);
        $html = $object->getTitle() . ' [' . $ref_id . ']';

        $actions = [];
        if (
            $this->getField()->getProperty(ilDclBaseFieldModel::PROP_DISPLAY_COPY_LINK_ACTION_MENU)
        ) {
            if ($this->access->checkAccess('delete', '', $ref_id) && $this->obj_def->allowLink($object->getType())) {
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'item_ref_id', $ref_id);
                $actions[] = $this->factory->link()->standard(
                    $this->lng->txt('link'),
                    $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjRootFolderGUI::class], 'link')
                );
            }
            if ($this->access->checkAccess('copy', '', $ref_id) && $this->obj_def->allowCopy($object->getType())) {
                $this->ctrl->setParameterByClass(ilObjectCopyGUI::class, 'source_id', $ref_id);
                $actions[] = $this->factory->link()->standard(
                    $this->lng->txt('copy'),
                    $this->ctrl->getLinkTargetByClass(ilObjectCopyGUI::class, 'initTargetSelection')
                );
            }
        }

        if (
            $this->getField()->getProperty(ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK) &&
            $this->access->checkAccess('read', '', $ref_id)
        ) {
            $link = (string) $this->static_url->builder()->build($object->getType(), new ReferenceId($ref_id));
            if ($actions === []) {
                $html = $this->renderer->render($this->factory->link()->standard($html, $link));
            } else {
                $html = $this->renderer->render(
                    $this->factory->dropdown()->standard(
                        array_merge([$this->factory->link()->standard($this->lng->txt('view'), $link)], $actions)
                    )->withLabel($html)
                );
            }
        }

        return $html;
    }
}
