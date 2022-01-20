<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Trait ilObjFilePreviewHandler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilObjFilePreviewHandler
{
    /**
     * Deletes the preview of the file object.
     */
    protected function deletePreview() : void
    {
        // only normal files are supported
        if ($this->getMode() !== self::MODE_OBJECT) {
            return;
        }
        ilPreview::deletePreview($this->getId());
    }

    /**
     * Creates a preview for the file object.
     * @param bool $force true, to force the creation of the preview; false, to create the preview
     *                    only if the file is newer.
     */
    protected function createPreview(bool $force = false) : void
    {
        // only normal files are supported
        if ($this->getMode() != self::MODE_OBJECT) {
            return;
        }
        ilPreview::createPreview($this, $force);
    }
}
