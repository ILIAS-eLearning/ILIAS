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

namespace ILIAS\WebDAV\Mount;

final class Document
{
    public function __construct(
        private int $id = 0,
        private string $title = '',
        private string $uploaded_instructions = '',
        private string $processed_instructions = '',
        private string $language = '',
        private string $creation_ts = '',
        private string $modification_ts = '',
        private int $owner_usr_id = 0,
        private int $last_modified_usr_id = 0,
        private int $sorting = 0
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUploadedInstructions(): string
    {
        return $this->uploaded_instructions;
    }

    public function getProcessedInstructions(): string
    {
        return $this->processed_instructions;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCreationTs(): string
    {
        return $this->creation_ts;
    }

    public function getModificationTs(): string
    {
        return $this->modification_ts;
    }

    public function getOwnerUsrId(): int
    {
        return $this->owner_usr_id;
    }

    public function getLastModificationUsrId(): int
    {
        return $this->last_modified_usr_id;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }
}
