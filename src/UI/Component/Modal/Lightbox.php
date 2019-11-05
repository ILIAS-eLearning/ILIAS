<?php
namespace ILIAS\UI\Component\Modal;

/**
 * Interface Lightbox
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Lightbox extends Modal
{

    /**
     * Get the lightbox pages of this modal
     *
     * @return LightboxPage[]
     */
    public function getPages();
}
