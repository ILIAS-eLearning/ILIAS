<?php
/**
 * Interface ilBiblAttributeFactoryInterface
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblAttributeFactoryInterface
{
    public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, int $object_id) : array;

    /**
     * @return \ilBiblAttributeInterface[]
     */
    public function getAttributesForEntry(ilBiblEntryInterface $entry) : array;

    /**
     * @param \ilBiblAttributeInterface[] $attributes
     * @return \ilBiblAttributeInterface[]
     */
    public function sortAttributes(array $attributes) : array;

    public function createAttribute(string $name, string $value, int $entry_id) : bool;
}
