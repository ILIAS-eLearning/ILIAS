<?php declare(strict_types=1);

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
 *********************************************************************/
 
namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class FileRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileRevision implements Revision
{
    protected bool $available = true;
    protected \ILIAS\ResourceStorage\Identification\ResourceIdentification $identification;
    protected int $version_number = 0;
    protected ?\ILIAS\ResourceStorage\Information\Information $information = null;
    protected int $owner_id = 0;
    protected string $title = '';

    /**
     * Revision constructor.
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
    }

    /**
     * @inheritDoc
     */
    public function getIdentification() : ResourceIdentification
    {
        return $this->identification;
    }

    public function setVersionNumber(int $version_number) : void
    {
        $this->version_number = $version_number;
    }

    public function getVersionNumber() : int
    {
        return $this->version_number;
    }

    /**
     * @inheritDoc
     */
    public function getInformation() : Information
    {
        return $this->information ?? new FileInformation();
    }

    public function setInformation(Information $information) : void
    {
        $this->information = $information;
    }

    /**
     * @inheritDoc
     */
    public function setUnavailable() : void
    {
        $this->available = false;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable() : bool
    {
        return $this->available;
    }

    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    public function setOwnerId(int $owner_id) : self
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @return $this|Revision
     */
    public function setTitle(string $title) : Revision
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
}
