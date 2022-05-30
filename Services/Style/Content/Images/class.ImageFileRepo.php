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

use ILIAS\Filesystem;
use ILIAS\Data\DataSize;
use ILIAS\FileUpload\FileUpload;
use Generator;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageFileRepo
{
    protected const DIR_PATH = "sty/sty_%id%/images";

    protected InternalDataService $factory;
    protected Filesystem\Filesystem $web_files;
    protected FileUpload $upload;

    public function __construct(
        InternalDataService $factory,
        Filesystem\Filesystem $web_files,
        FileUpload $upload
    ) {
        $this->web_files = $web_files;
        $this->factory = $factory;
        $this->upload = $upload;
    }

    // get image directory
    protected function dir(int $style_id) : string
    {
        return str_replace("%id%", (string) $style_id, self::DIR_PATH);
    }

    /**
     * Get images of style
     * @param int $style_id
     * @return Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages(
        int $style_id
    ) : Generator {
        $dir = $this->dir($style_id);
        if ($this->web_files->hasDir($dir)) {
            foreach ($this->web_files->listContents($dir) as $meta) {
                if ($meta->isFile()) {
                    $size = $this->web_files->getSize(
                        $meta->getPath(),
                        DataSize::KB
                    );
                    $image_size = getimagesize($this->getWebPath($meta->getPath()));
                    $width = $image_size[0] ?? 0;
                    $height = $image_size[1] ?? 0;
                    yield $this->factory->image(
                        $meta->getPath(),
                        $size,
                        $width,
                        $height
                    );
                }
            }
        }
    }

    // get full web path for relative file path
    public function getWebPath(string $path) : string
    {
        return ILIAS_WEB_DIR . "/" . CLIENT_ID . "/" . $path;
    }

    // get image data object by filename
    public function getByFilename(int $style_id, string $filename) : ?Image
    {
        /** @var Image $i */
        foreach ($this->getImages($style_id) as $i) {
            if ($i->getFilename() == $filename) {
                return $i;
            }
        }
        return null;
    }

    /**
     * @param int    $style_id
     * @throws Filesystem\Exception\IOException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function uploadImage(int $style_id) : void
    {
        $upload = $this->upload;

        $dir = $this->dir($style_id);
        if (!$this->web_files->hasDir($dir)) {
            $this->web_files->createDir($dir);
        }
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            $upload->process();
            $result = array_values($upload->getResults())[0];
            if ($result->getStatus() == ProcessingStatus::OK) {
                $upload->moveFilesTo($dir, Location::WEB);
            }
        }
    }

    // delete image
    public function deleteImageByFilename(int $style_id, string $filename) : void
    {
        $dir = $this->dir($style_id);
        $this->web_files->delete($dir . "/" . $filename);
    }
}
