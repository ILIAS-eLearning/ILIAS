<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Collector;

/**
 * Class AbstractBaseCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseCollector implements Collector
{

    /**
     * @var bool
     */
    private $has_been_collected = false;


    private function setCollected() : void
    {
        $this->has_been_collected = true;
    }


    /**
     * @return bool
     */
    public function hasBeenCollected() : bool
    {
        return $this->has_been_collected;
    }


    public function collectOnce() : void
    {
        if (!$this->hasBeenCollected()) {
            $this->collectStructure();
            $this->prepareItemsForUIRepresentation();
            $this->filterItemsByVisibilty(false);
            $this->cleanupItemsForUIRepresentation();
            $this->sortItemsForUIRepresentation();
            $this->setCollected();
        }
    }
}
