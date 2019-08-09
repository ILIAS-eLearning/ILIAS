<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

/**
 * Class Factory
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Factory implements \ILIAS\UI\Component\Dropzone\File\Factory
{

    /**
     * @inheritdoc
     */
    public function standard($url)
    {
        return new Standard($url);
    }

    /**
     * @inheritdoc
     */
    public function wrapper($url, $content)
    {
        return new Wrapper($url, $content);
    }
}
