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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetNonAvailablePool
{
    public const UNAVAILABILITY_STATUS_LOST = 'lost';
    public const UNAVAILABILITY_STATUS_TRASHED = 'trashed';

    /**
     * @var string
     */
    protected $unavailabilityStatus;

    /**
     * @var integer
     */
    protected $id;

    /** @var int|null */
    protected $ref_id = null;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getUnavailabilityStatus(): string
    {
        return $this->unavailabilityStatus;
    }

    /**
     * @param string $unavailabilityStatus
     */
    public function setUnavailabilityStatus($unavailabilityStatus)
    {
        $this->unavailabilityStatus = $unavailabilityStatus;
    }

    public function getRefId(): ?int
    {
        return $this->ref_id;
    }

    public function setRefId(?int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    /**
     * @param array $row
     */
    public function assignDbRow($row)
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
