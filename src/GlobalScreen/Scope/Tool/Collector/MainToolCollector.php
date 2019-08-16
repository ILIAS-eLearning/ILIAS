<?php namespace ILIAS\GlobalScreen\Scope\Tool\Collector;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\BaseTypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
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
     * @var ItemInformation
     */
    private $information;
    /**
     * @var TypeInformationCollection
     */
    private $type_information_collection;
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
    public function __construct(array $providers, ItemInformation $information = null)
    {
        $this->providers = $providers;
        $this->information = $information;
        $this->type_information_collection = new TypeInformationCollection();
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
        array_walk($this->tools, function (Tool $tool) {
            $this->applyTypeHandler($tool);
        });
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
     * @param isItem $item
     *
     * @return isItem
     */
    private function applyTypeHandler(isItem $item) : isItem
    {
        $item = $this->getHandlerForItem($item)->enrichItem($item);

        return $item;
    }


    /**
     * @param isItem $item
     *
     * @return TypeHandler
     */
    public function getHandlerForItem(isItem $item) : TypeHandler
    {
        /**
         * @var $handler TypeHandler
         */
        $type = get_class($item);
        $type_information = $this->type_information_collection->get($type);
        if (is_null($type_information)) {
            return new BaseTypeHandler();
        }
        $handler = $type_information->getTypeHandler();

        return $handler;
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
