<?php
/**
 * Interface ilBiblAttributeFactoryInterface
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblAttributeFactoryInterface
{

    /**
     * @param \ilBiblFieldInterface $field
     * @param int                   $object_id
     *
     * @return array
     */
    public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, $object_id);


    /**
     * @param \ilBiblEntryInterface $entry
     *
     * @return \ilBiblAttributeInterface[]
     */
    public function getAttributesForEntry(ilBiblEntryInterface $entry);


    /**
     * @param \ilBiblAttributeInterface[]  $attributes
     *
     * @return \ilBiblAttributeInterface[]
     */
    public function sortAttributes(array $attributes);


    /**
     * @param string $name
     * @param string $value
     * @param integer $entry_id
     *
     * @return true on success | false on failure
     */
    public function createAttribute($name, $value, $entry_id);
}
