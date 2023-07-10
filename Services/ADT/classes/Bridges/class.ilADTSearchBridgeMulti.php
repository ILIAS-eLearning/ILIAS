<?php

declare(strict_types=1);

/**
 * Class ilADTSearchBridgeMulti
 */
abstract class ilADTSearchBridgeMulti extends ilADTSearchBridgeSingle
{
    protected function setDefinition(ilADTDefinition $a_adt_def): void
    {
        if ($this->isValidADTDefinition($a_adt_def)) {
            $def = $this->convertADTDefinitionToMulti($a_adt_def);
            $this->adt = ilADTFactory::getInstance()->getInstanceByDefinition($def);
            return;
        }
        throw new InvalidArgumentException('ilADTSearchBridge type mismatch.');
    }

    abstract protected function convertADTDefinitionToMulti(ilADTDefinition $a_adt_def): ilADTDefinition;
}
