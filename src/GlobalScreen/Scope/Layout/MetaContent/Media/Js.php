<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class Js
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Js extends AbstractMediaWithPath
{

    /**
     * @var bool
     */
    private $add_version_number = true;
    /**
     * @var int
     */
    private $batch = 2;


    /**
     * Js constructor.
     *
     * @param string $content
     * @param bool   $add_version_number
     * @param int    $batch
     */
    public function __construct(string $content, string $version, bool $add_version_number = true, int $batch = 2)
    {
        parent::__construct($content, $version);
        $this->add_version_number = $add_version_number;
        $this->batch = $batch;
    }


    /**
     * @return bool
     */
    public function addVersionNumber() : bool
    {
        return $this->add_version_number;
    }


    /**
     * @return int
     */
    public function getBatch() : int
    {
        return $this->batch;
    }
}
