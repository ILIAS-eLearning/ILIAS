<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Information\Repository;

use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * Interface InformationRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface InformationRepository extends LockingRepository, PreloadableRepository
{

    /**
     * @return mixed
     */
    public function blank();


    /**
     * @param Information $information
     * @param Revision    $revision
     */
    public function store(Information $information, Revision $revision) : void;


    /**
     * @param Revision $revision
     *
     * @return Information
     */
    public function get(Revision $revision) : Information;


    /**
     * @param Information $information
     * @param Revision    $revision
     */
    public function delete(Information $information, Revision $revision) : void;
}
