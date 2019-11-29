<?php

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;

/**
 * Class Inline
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Inline extends Icon implements C\Symbol\Icon\Inline
{

    /**
     * @var    string
     */
    private $base_64;
    /**
     * @var string
     */
    private $mime_type;


    public function __construct(string $base_64, string $aria_label, string $size, bool $is_disabled, string $mime_type)
    {
        $this->checkArgIsElement(
            'size',
            $size,
            self::$possible_sizes,
            implode('/', self::$possible_sizes)
        );
        $this->name = 'inline';
        $this->mime_type = $mime_type;
        $this->base_64 = $base_64;
        $this->aria_label = $aria_label;
        $this->size = $size;
        $this->is_disabled = $is_disabled;
    }


    /**
     * @inheritDoc
     */
    public function getBase64Data() : string
    {
        //src="data:image/gif;base64,R0lGOD...
        return $this->base_64;
    }


    public function getMimeType() : string
    {
        return $this->mime_type;
    }
}
