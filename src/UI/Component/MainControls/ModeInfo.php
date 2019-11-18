<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;

/**
 * Interface ModeInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ModeInfo
{

    public function getModeTitle() : string;


    public function getCloseAction() : URI;
}
