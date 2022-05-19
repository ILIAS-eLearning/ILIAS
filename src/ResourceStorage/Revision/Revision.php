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
 *********************************************************************/
 
namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class FileRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Revision
{
    public const STATUS_ACTIVE = 1;

    public function getIdentification() : ResourceIdentification;

    public function getVersionNumber() : int;

    public function getInformation() : Information;

    public function setInformation(Information $information) : void;

    public function setUnavailable() : void;

    public function isAvailable() : bool;

    public function getOwnerId() : int;

    public function setTitle(string $title) : Revision;

    public function getTitle() : string;
}
