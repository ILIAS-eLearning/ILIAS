<?php

declare(strict_types=1);

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
 * Class ilStudyProgrammeAdvancedMetadataRecord
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStudyProgrammeAdvancedMetadataRecord
{
    protected int $id;
    protected int $type_id = 0;
    protected int $rec_id = 0;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTypeId(): int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type_id): void
    {
        $this->type_id = $type_id;
    }

    public function getRecId(): int
    {
        return $this->rec_id;
    }

    public function setRecId(int $rec_id): void
    {
        $this->rec_id = $rec_id;
    }
}
