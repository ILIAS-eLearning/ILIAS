<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context\Provider;

use ILIAS\GlobalScreen\Scope\Tool\Context\ToolContext;

/**
 * Class ContextAwareDynamicProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractContextAwareDynamicProvider implements ContextAwareDynamicProvider
{

    /**
     * @inheritdoc
     */
    abstract public function enrichContextWithCurrentSituation(ToolContext $context) : ToolContext;
}
