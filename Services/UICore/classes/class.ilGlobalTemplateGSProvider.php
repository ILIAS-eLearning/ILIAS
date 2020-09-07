<?php

use ILIAS\GlobalScreen\Scope\Context\ContextInterface;

/**
 * Class ilGlobalTemplateGSProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalTemplateGSProvider extends AbstractDynamicContextProvider
{

    /**
     * @inheritDoc
     */
    public function getGeneralContextsForComponent() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function enrichContextWithCurrentSituation(ContextInterface $context) : ContextInterface
    {
        return $context;
    }
}
