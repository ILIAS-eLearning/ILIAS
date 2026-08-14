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

use ILIAS\HTTP\Services;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilDclBaseRecordRepresentation
{
    protected Factory $factory;
    protected ilDclBaseRecordFieldModel $record_field;
    protected ilLanguage $lng;
    protected ilAccess $access;
    protected ilCtrl $ctrl;
    protected Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected Renderer $renderer;
    protected ilObjUser $user;

    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->user = $DIC->user();

        $this->record_field = $record_field;
    }

    public function getHTML(bool $link = true, array $options = []): string
    {
        return (string) $this->getRecordField()->getValue();
    }

    public function getSingleHTML(?array $options = null, bool $link = true): string
    {
        return $this->getHTML($link, $options);
    }

    public function getConfirmationHTML(): string
    {
        return $this->getHTML();
    }

    public function getRecordField(): ilDclBaseRecordFieldModel
    {
        return $this->record_field;
    }

    public function getField(): ilDclBaseFieldModel
    {
        return $this->record_field->getField();
    }

    public function getRecord(): ilDclBaseRecordModel
    {
        return $this->record_field->getRecord();
    }
}
