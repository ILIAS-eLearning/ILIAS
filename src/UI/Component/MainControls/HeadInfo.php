<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
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
     * @param URI $uri
     *
     * @return HeadInfo
     */
    public function withCloseAction(URI $uri) : HeadInfo;


    /**
     * @return URI|null
     */
    public function getCloseAction() : ?URI;


    /**
     * @param bool $is_interruptive
     *
     * @return HeadInfo
     */
    public function withInterruptive(bool $is_interruptive) : HeadInfo;


    /**
     * @return bool
     */
    public function isInterruptive() : bool;
}
