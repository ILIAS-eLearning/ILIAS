<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Preloader;

/**
 * Interface RepositoryPreloader
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RepositoryPreloader
{
    public function preload(array $identification_strings) : void;
}
