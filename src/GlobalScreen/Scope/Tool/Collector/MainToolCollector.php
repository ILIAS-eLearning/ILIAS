<?php namespace ILIAS\GlobalScreen\Scope\Tool\Collector;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class MainToolCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainToolCollector
{

    /**
     * @var array
     */
    private $tools;
    /**
     * @var DynamicToolProvider[]
     */
    private $providers = [];


    /**
     * MainToolCollector constructor.
     *
     * @param DynamicToolProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
        $this->tools = [];
        $this->initTools();
    }


    private function initTools()
    {
        global $DIC;
        $called_contexts = $DIC->globalScreen()->tool()->context()->stack();

        foreach ($this->providers as $provider) {
            $context_collection = $provider->isInterestedInContexts();
            if ($context_collection->hasMatch($called_contexts)) {
                $this->tools = array_merge($this->tools, $provider->getToolsForContextStack($called_contexts));
            }
        }

        $this->tools = array_filter($this->tools, $this->getVisibleFilter());
    }


    /**
     * @return Tool[]
     */
    public function getTools() : array
    {
        return $this->tools;
    }


    public function hasTools() : bool
    {
        return count($this->tools) > 0;
    }


    /**
     * @return Closure
     */
    private function getVisibleFilter() : Closure
    {
        return function (isItem $tool) {
            return ($tool->isAvailable() && $tool->isVisible());
        };
    }
}
