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

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Filesystem\Visibility;
use ILIAS\Filesystem\Filesystem;

/**
 * Rendered MathJax image
 * Supports image types SVG or PNG
 * Files are stored in the web file system of ilias
 */
class ilMathJaxImage
{
    private const TYPE_PNG = 'png';
    private const TYPE_SVG = 'svg';

    /**
     * Webspace filesystem where the cached images are stored
     * @var Filesystem
     */
    protected Filesystem $fs;

    /**
     * @var string Relative path from the ilias web directory
     */
    protected string $basepath = '/temp/tex';

    /**
     * @var string Given latex code
     */
    protected string $tex;

    /**
     * @var string File suffix for the given type
     */
    protected string $suffix;

    /**
     * @var string Salt for the filename generation, depending on the dpi parameter
     */
    protected string $salt;

    /**
     * @param string $a_tex  latex code
     * @param string $a_type image type ('png' or 'svg')
     * @param int    $a_dpi  dpi of rendered image
     */
    public function __construct(string $a_tex, string $a_type, int $a_dpi)
    {
        global $DIC;

        $this->fs = $DIC->filesystem()->web();
        $this->tex = $a_tex;

        switch ($a_type) {
            case self::TYPE_PNG:
                $this->suffix = '.png';
                break;
            case self::TYPE_SVG:
                $this->suffix = '.svg';
                break;
            default:
                throw new ilMathJaxException('imagetype not supported');
        }

        $this->salt = '#' . $a_dpi;
    }

    /**
     * Get the relative directory path of the image
     */
    protected function filedir(): string
    {
        $hash = md5($this->tex . $this->salt);
        return $this->basepath
        . '/' . substr($hash, 0, 4)
        . '/' . substr($hash, 4, 4);
    }

    /**
     * Get the relative file path of the image
     */
    protected function filepath(): string
    {
        $hash = md5($this->tex . $this->salt);
        return $this->filedir() . '/' . $hash . $this->suffix;
    }

    /**
     * Get the absolute path of the image
     */
    public function absolutePath(): string
    {
        return CLIENT_WEB_DIR . $this->filepath();
    }

    /**
     * Check if an image is cached
     */
    public function exists(): bool
    {
        return $this->fs->has($this->filepath());
    }

    /**
     * Read the content of a cached image
     */
    public function read(): string
    {
        return $this->fs->read($this->filepath());
    }

    /**
     * Save the content of a cached image
     * @param string $a_content image content
     */
    public function write(string $a_content): void
    {
        // set the directory access of the whole relative file to visible
        // this is needed if TeX is used in certificates
        // the ILIAS java server must have read access to the files for the PDF generation
        // it may run with a different user account
        $dir = '';
        foreach (explode('/', $this->filedir()) as $part) {
            if (!empty($part)) {
                $dir = $dir . '/' . $part;
            }
            if (!$this->fs->hasDir($dir)) {
                $this->fs->createDir($dir, Visibility::PUBLIC_ACCESS);
            } else {
                $this->fs->setVisibility($dir, Visibility::PUBLIC_ACCESS);
            }
        }
        $this->fs->put($this->filepath(), $a_content);
    }

    /**
     * Get the total size of the cache with an appropriate unit for display
     */
    public function getCacheSize(): string
    {
        $size = 0;
        if ($this->fs->hasDir($this->basepath)) {
            foreach ($this->fs->finder()->in([$this->basepath])->files() as $meta) {
                $size += $this->fs->getSize($meta->getPath(), 1)->inBytes();
            }
        }

        $type = array("K", "M", "G", "T", "P", "E", "Z", "Y");
        $size /= 1000;
        $counter = 0;
        while ($size >= 1000) {
            $size /= 1000;
            $counter++;
        }

        return (round($size, 1) . " " . $type[$counter] . "B");
    }

    /**
     * Delete all files from the cache
     */
    public function clearCache(): void
    {
        $this->fs->deleteDir($this->basepath);
    }
}
