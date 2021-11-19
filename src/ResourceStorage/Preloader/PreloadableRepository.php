<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Preloader;

/**
 * Interface PreloadableRepository
 * @internal
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PreloadableRepository
{

    public function preload(array $identification_strings) : void;

    public function populateFromArray(array $data) : void;
}
