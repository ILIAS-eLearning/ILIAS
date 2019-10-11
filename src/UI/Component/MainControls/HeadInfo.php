<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Component;

/**
 * Interface HeadInfo
 *
 * The interface describes a HeadInfo Component
 *
 * @package ILIAS\UI\Component\MainControls
 */
interface HeadInfo extends Component
{

    /**
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param string $info_message
     *
     * @return HeadInfo
     */
    public function withDescription(string $info_message) : HeadInfo;


    /**
     * @return string
     */
    public function getDescription() : string;


    /**
     * @param Button $button
     *
     * @return mixed
     */
    public function withCloseButton(Button $button);


    /**
     * @return Button
     */
    public function getCloseButton() : Button;
}
