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

use DateTimeImmutable;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class NullRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullRevision implements Revision
{
    private \ILIAS\ResourceStorage\Identification\ResourceIdentification $identification;

    /**
     * NullRevision constructor.
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

    /**
     * @inheritDoc
     */
    public function getVersionNumber() : int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getCreationDate() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * @inheritDoc
     */
    public function getInformation() : Information
    {
        return new FileInformation();
    }

    public function setInformation(Information $information) : void
    {
    }

    public function setUnavailable() : void
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    public function isAvailable() : bool
    {
        return false;
    }

    public function getOwnerId() : int
    {
        return 0;
    }

    public function setTitle(string $title) : Revision
    {
        // do nothing
        return $this;
    }

    public function getTitle() : string
    {
        return '';
    }
}
