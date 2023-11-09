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

namespace ILIAS\EmployeeTalk\Metadata;

interface MetadataHandlerInterface
{
    public function getEditForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        string $form_action,
        string $submit_command,
        string $submit_label
    ): EditFormInterface;

    public function getDisabledEditForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id
    ): EditFormInterface;

    public function copyValues(
        string $from_type,
        int $from_id,
        string $to_type,
        int $to_id,
        string $subtype
    ): void;

    public function attachSelectionToForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        \ilPropertyFormGUI $form
    ): void;

    public function saveSelectionFromForm(
        string $type,
        int $id,
        string $subtype,
        int $sub_id,
        \ilPropertyFormGUI $form
    ): void;
}
