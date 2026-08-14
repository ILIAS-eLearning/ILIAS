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

use ILIAS\DI\UIServices;
use ILIAS\ResourceStorage\Services;

class ilDclFileRecordRepresentation extends ilDclBaseRecordRepresentation
{
    protected Services $irss;
    protected UIServices $ui_services;

    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        global $DIC;
        parent::__construct($record_field);
        $this->irss = $DIC->resourceStorage();
        $this->ui_services = $DIC->ui();
    }

    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->record_field->getValue();

        if ($value === null) {
            return '';
        }

        $rid = $this->irss->manage()->find($value);
        if ($rid === null || null === $revision = $this->irss->manage()->getCurrentRevision($rid)) {
            return $this->lng->txt('file_not_found');
        }

        if ($link) {
            $link_component = $this->ui_services->factory()->link()->standard(
                $revision->getTitle(),
                $this->buildDownloadLink()
            );

            return $this->ui_services->renderer()->render($link_component);
        }

        return $revision->getTitle();
    }

    protected function buildDownloadLink(): string
    {
        $record_field = $this->getRecordField();
        $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, 'record_id', $record_field->getRecord()->getId());
        $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, 'field_id', $record_field->getField()->getId());
        return $this->ctrl->getLinkTargetByClass(ilDclRecordListGUI::class, 'sendFile');
    }
}
