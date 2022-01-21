<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class ModeInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModeInfo implements MainControls\ModeInfo
{
    use ComponentHelper;

    private string $mode_title;
    private URI $close_action;

    public function __construct(string $mode_title, URI $close_action)
    {
        $this->mode_title = $mode_title;
        $this->close_action = $close_action;
    }

    public function getModeTitle() : string
    {
        return $this->mode_title;
    }

    public function getCloseAction() : URI
    {
        return $this->close_action;
    }
}
