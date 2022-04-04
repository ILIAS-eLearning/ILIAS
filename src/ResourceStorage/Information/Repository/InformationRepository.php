<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Information\Repository;

use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface InformationRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface InformationRepository extends LockingRepository, PreloadableRepository
{
    public function blank() : Information;


    public function store(Information $information, Revision $revision) : void;


    public function get(Revision $revision) : Information;


    public function delete(Information $information, Revision $revision) : void;
}
