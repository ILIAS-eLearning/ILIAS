<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class OnLoadCode
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OnLoadCode extends AbstractMedia
{

    /**
     * @var int
     */
    private $batch = 2;


    /**
     * OnLoadCode constructor.
     *
     * @param string $content
     * @param int    $batch
     */
    public function __construct(string $content, int $batch = 2)
    {
        parent::__construct($content);
        $this->batch = $batch;
    }


    /**
     * @return int
     */
    public function getBatch() : int
    {
        return $this->batch;
    }
}
