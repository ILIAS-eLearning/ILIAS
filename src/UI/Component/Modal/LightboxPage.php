<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;

/**
 * Interface LightboxPage
 *
 * A lightbox page represents a page displaying a media element, such as image, video or text.
 */
interface LightboxPage
{
    /**
     * Get the title of this page, displayed as title in the lightbox modal.
     */
    public function getTitle() : string;

    /**
     * Get the component representing the media item to be displayed in the modals
     * content section, e.g. an image.
     */
    public function getComponent() : Component;
}
