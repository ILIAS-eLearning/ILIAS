<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Information\Repository;

use ILIAS\MainMenu\Storage\Information\Information;
use ILIAS\MainMenu\Storage\Revision\Revision;

/**
 * Interface InformationRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface InformationRepository
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
