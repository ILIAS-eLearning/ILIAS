<?php

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Component;

/**
 * Interface ModeInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ModeInfo extends Component
{

    public function getModeTitle() : string;


    public function getCloseAction() : URI;
}
