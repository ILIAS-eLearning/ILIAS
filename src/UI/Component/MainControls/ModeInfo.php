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
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param string $info_message
     *
     * @return ModeInfo
     */
    public function withDescription(string $info_message) : ModeInfo;


    /**
     * @return string
     */
    public function getDescription() : string;


    /**
     * @return \ILIAS\UI\Component\Button\Close
     */
    public function getCloseButton() : \ILIAS\UI\Component\Button\Close;
}
