<?php
namespace ILIAS\UI\Component\Modal;

/**
 * Interface LightboxDescriptionEnabledPage
 *
 * A lightbox descriptive page behaves like a LightBox with an additional description.
 */
interface LightboxDescriptionEnabledPage extends LightboxPage
{
    /**
     * Get the description of this page, displayed along with the media item
     *
     * @return string
     */
    public function getDescription() : string;
}
