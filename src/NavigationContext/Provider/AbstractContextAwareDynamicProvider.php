<?php namespace ILIAS\NavigationContext\Provider;

use ILIAS\NavigationContext\ContextInterface;

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
    abstract public function enrichContextWithCurrentSituation(ContextInterface $context) : ContextInterface;
}
