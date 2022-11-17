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

namespace ILIAS\ResourceStorage\Information\Repository;

use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * Interface InformationRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface InformationRepository extends LockingRepository, PreloadableRepository
{
    public function blank(): Information;


    public function store(Information $information, Revision $revision): void;


    public function get(Revision $revision): Information;


    public function delete(Information $information, Revision $revision): void;
}
