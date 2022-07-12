<?php declare(strict_types=1);

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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access;
use ILIAS\Filesystem;
use ilShellUtil;
use Generator;

/**
 * Main business logic for content style images
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageManager
{
    protected ImageFileRepo $repo;
    protected Access\StyleAccessManager $access_manager;
    protected int $style_id;

    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        ImageFileRepo $repo
    ) {
        $this->repo = $repo;
        $this->access_manager = $access_manager;
        $this->style_id = $style_id;
    }

    /**
     * Get images of style
     * @return Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages() : Generator
    {
        return $this->repo->getImages($this->style_id);
    }

    public function filenameExists(string $filename) : bool
    {
        /** @var Image $i */
        foreach ($this->getImages() as $i) {
            if ($i->getFilename() == $filename) {
                return true;
            }
        }
        return false;
    }

    // get web data dir path for output
    public function getWebPath(Image $image) : string
    {
        return $this->repo->getWebPath($image->getPath());
    }

    // get image data object by filename
    public function getByFilename(string $filename) : Image
    {
        return $this->repo->getByFilename($this->style_id, $filename);
    }

    // resize image
    public function resizeImage(
        string $filename,
        int $width,
        int $height,
        bool $constrain_proportions
    ) : void {
        if ($this->filenameExists($filename)) {
            $file = $this->getWebPath($this->getByFilename($filename));
            ilShellUtil::resizeImage(
                $file,
                $file,
                $width,
                $height,
                $constrain_proportions
            );
        }
    }

    // resize image
    public function supportsResize(
        Image $image
    ) : bool {
        // for svg, see
        // https://stackoverflow.com/questions/6532261/how-do-you-get-the-width-and-height-of-an-svg-picture-in-php
        if (in_array(
            strtolower(pathinfo($image->getFilename(), PATHINFO_EXTENSION)),
            ["jpg", "jpeg", "gif", "png"]
        )) {
            return true;
        }
        return false;
    }

    // upload image
    public function uploadImage() : void
    {
        $this->repo->uploadImage($this->style_id);
    }

    public function deleteByFilename(string $filename) : void
    {
        $this->repo->deleteImageByFilename($this->style_id, $filename);
    }
}
