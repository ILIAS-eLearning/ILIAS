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

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilDclFileRecordPresentation extends ilDclBaseRecordRepresentation
{
    use ilDclFileFieldHelper;

    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\DI\UIServices $ui_services;

    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        global $DIC;
        parent::__construct($record_field);
        $this->irss = $DIC->resourceStorage();
        $this->ui_services = $DIC->ui();
    }

    public function getSingleHTML(?array $options = null, bool $link = true): string
    {
        return $this->getHTML(true, $options ?? []);
    }

    public function getHTML(bool $link = true, array $options = []): string
    {
        $rid_string = $this->record_field->getValue();

        if ($rid_string === null || is_array($rid_string)) {
            return '';
        }

        $title = $this->valueToFileTitle($rid_string);

        if ($title === '') {
            return $this->lng->txt('file_not_found');
        }

        if ($link) {
            $link_component = $this->ui_services->factory()->link()->standard(
                $title,
                $this->buildDownloadLink()
            );

            return $this->ui_services->renderer()->render($link_component);
        }

        return $title;
    }

    public function parseFormInput($value)
    {
        if ($value === null || is_array($value)) {
            return '';
        }
        return $this->valueToFileTitle($value);
    }

    private function buildDownloadLink(): string
    {
        $record_field = $this->getRecordField();

        $this->ctrl->setParameterByClass(
            ilDclRecordListGUI::class,
            "record_id",
            $record_field->getRecord()->getId()
        );
        $this->ctrl->setParameterByClass(
            ilDclRecordListGUI::class,
            "field_id",
            $record_field->getField()->getId()
        );
        return $this->ctrl->getLinkTargetByClass(
            ilDclRecordListGUI::class,
            "sendFile"
        );
    }
}
