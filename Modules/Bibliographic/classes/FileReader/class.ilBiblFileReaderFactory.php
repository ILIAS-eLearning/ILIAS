<?php

/**
 * Class ilBiblFileReaderFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFileReaderFactory implements ilBiblFileReaderFactoryInterface
{
    public function getByType(
        int $type,
        ilBiblEntryFactoryInterface $entry_factory,
        ilBiblFieldFactoryInterface $field_factory,
        ilBiblAttributeFactoryInterface $attribute_factory
    ) : ilBiblFileReaderInterface {
        switch ($type) {
            case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
                return new ilBiblTexFileReader($entry_factory, $field_factory, $attribute_factory);
            case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
                return new ilBiblRisFileReader($entry_factory, $field_factory, $attribute_factory);
            default:
                throw new ilException("bibliografic type not found");
        }
    }
}
