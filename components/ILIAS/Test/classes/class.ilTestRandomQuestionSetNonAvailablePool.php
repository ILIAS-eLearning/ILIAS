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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestRandomQuestionSetNonAvailablePool
{
    public const UNAVAILABILITY_STATUS_LOST = 'lost';
    public const UNAVAILABILITY_STATUS_TRASHED = 'trashed';

    protected string $unavailability_status;
    protected int $id;
    protected ?int $ref_id = null;
    protected string $title;
    protected string $path;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getUnavailabilityStatus(): string
    {
        return $this->unavailability_status;
    }

    public function setUnavailabilityStatus(string $unavailability_status): void
    {
        $this->unavailability_status = $unavailability_status;
    }

    public function getRefId(): ?int
    {
        return $this->ref_id;
    }

    public function setRefId(?int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    public function assignDbRow(array $row): void
    {
        foreach ($row as $field => $value) {
            switch ($field) {
                case 'pool_fi': $this->setId($value);
                    break;
                case 'pool_ref_id': $this->setRefId($value ? (int) $value : null);
                    break;
                case 'pool_title': $this->setTitle($value);
                    break;
                case 'pool_path': $this->setPath($value);
                    break;
            }
        }
    }
}
