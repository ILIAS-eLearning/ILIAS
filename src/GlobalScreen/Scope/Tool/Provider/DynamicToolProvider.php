<?php namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\NavigationContext\Stack\CalledContexts;
use ILIAS\NavigationContext\Stack\ContextCollection;

/**
 * Interface DynamicToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicToolProvider extends Provider
{

    const PURPOSE_TOOLS = "tools";


    /**
     * @return ContextCollection
     */
    public function isInterestedInContexts() : ContextCollection;


    /**
     * @param CalledContexts $called_contexts
     *
     * @return Tool[] These Slates
     * can be passed to the MainMenu dynamic for a specific location/context.
     * @see DynamicProvider
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array;
}
