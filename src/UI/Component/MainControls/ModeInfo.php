<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Component;

/**
 * Interface ModeInfo
 *
 * The interface describes a ModeInfo Component
 *
 * @package ILIAS\UI\Component\MainControls
 */
interface ModeInfo extends Component
{

    /**
     * Returns the title of the currently available mode.
     *
     * @return string
     */
    public function getTitle() : string;


    /**
     * The mode can be specified with further information displayed to the user.
     *
     * @param string $info_message
     *
     * @return ModeInfo
     */
    public function withDescription(string $info_message) : ModeInfo;


    /**
     * Returns a specification of the current mode, if any.
     *
     * @return string
     */
    public function getDescription() : string;


    /**
     * Returns the Close button, which must close the current mode.
     *
     * @return \ILIAS\UI\Component\Button\Close
     */
    public function getCloseButton() : \ILIAS\UI\Component\Button\Close;
}
