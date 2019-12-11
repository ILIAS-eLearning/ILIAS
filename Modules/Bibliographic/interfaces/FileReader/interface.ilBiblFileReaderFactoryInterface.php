<?php

/**
 * Interface ilBiblFileReaderFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderFactoryInterface
{

    /**
     * @param                                 $type
     *
     * @param ilBiblEntryFactoryInterface     $entry_factory
     * @param ilBiblFieldFactoryInterface     $field_factory
     * @param ilBiblAttributeFactoryInterface $attribute_factory
     *
     * @return ilBiblFileReaderInterface
     */
    public function getByType($type, ilBiblEntryFactoryInterface $entry_factory, ilBiblFieldFactoryInterface $field_factory, ilBiblAttributeFactoryInterface $attribute_factory);
}
