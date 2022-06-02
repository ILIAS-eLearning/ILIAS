<?php declare(strict_types=1);

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
