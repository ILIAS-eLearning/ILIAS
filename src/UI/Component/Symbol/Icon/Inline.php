<?php

namespace ILIAS\UI\Component\Symbol\Icon;

/**
 * This describes the behavior of an inline icon.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Inline extends Icon
{

    /**
     * Return the base64 content of the icon
     */
    public function getBase64Data() : string;


    public function getMimeType() : string;
}
