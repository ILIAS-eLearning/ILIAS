<?php
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

/**
 * Represents an image that was created from a preview renderer and that can be
 * further processed to create the preview.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilRenderedImage
{
    /**
     * The absolute path to the image.
     */
    private ?string $img_path = null;
    
    /**
     * Defines whether the image is temporary and can be deleted after
     * the preview was created from the image.
     */
    private bool $is_temporary = true;
    
    /**
     * Constructor
     *
     * @param string $img_path The absolute path to the image.
     * @param bool $is_temporary Defines whether the image is temporary and can be deleted after the preview was created.
     */
    public function __construct(string $img_path, bool $is_temporary = true)
    {
        $this->img_path = $img_path;
        $this->is_temporary = $is_temporary;
    }
    
    /**
     * Gets the absolute path to the rendered image.
     *
     * @return string The absolute path to the rendered image.
     */
    public function getImagePath() : ?string
    {
        return $this->img_path;
    }
    
    /**
     * Defines whether the image is temporary and can be deleted after the preview was created.
     *
     * @return bool true, if the image is temporary and can be deleted after the preview was created; otherwise, false.
     */
    public function isTemporary() : bool
    {
        return $this->is_temporary;
    }
    
    /**
     * Deletes the image file if it is temporary.
     */
    public function delete() : void
    {
        // only delete if not temporary
        if ($this->isTemporary() && is_file($this->getImagePath())) {
            @unlink($this->getImagePath());
        }
    }
}
