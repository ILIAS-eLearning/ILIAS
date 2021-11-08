<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File as F;

/**
 * Class Factory
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Factory implements F\Factory
{

    /**
     * @inheritdoc
     */
    public function standard(string $url) : F\Standard
    {
        return new Standard($url);
    }

    /**
     * @inheritdoc
     */
    public function wrapper(string $url, $content) : F\Wrapper
    {
        return new Wrapper($url, $content);
    }
}
