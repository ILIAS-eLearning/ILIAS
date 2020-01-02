<?php

/**
 * Interface ilBiblFieldFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldFactoryInterface
{

    /**
     * @param int        $type MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
     *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
     * @param     string $identifier
     *
     * @throws \ilException if a wrong $type is passed or field is not found
     *
     * @return \ilBiblFieldInterface
     */
    public function getFieldByTypeAndIdentifier(int $type, string $identifier) : ilBiblFieldInterface;


    /**
     * @param int        $type MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
     *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
     * @param     string $identifier
     *
     * @throws \ilException if a wrong $type is passed
     *
     * @return \ilBiblFieldInterface
     */
    public function findOrCreateFieldByTypeAndIdentifier(int $type, string $identifier) : ilBiblFieldInterface;


    /**
     * @param int $obj_id
     *
     * @return ilBiblFieldInterface[] instances of all known standard-fields for the given type
     */
    public function getAvailableFieldsForObjId(int $obj_id) : array;


    /**
     * @param \ilBiblTypeInterface           $type
     * @param \ilBiblTableQueryInfoInterface $queryInfo
     *
     * @return \ilBiblField[]
     */
    public function filterAllFieldsForType(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) : array;


    /**
     * @param \ilBiblTypeInterface                $type
     * @param \ilBiblTableQueryInfoInterface|null $queryInfo
     *
     * @return array
     */
    public function filterAllFieldsForTypeAsArray(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) : array;


    /**
     * @return \ilBiblTypeInterface
     */
    public function getType() : ilBiblTypeInterface;


    /**
     * @param int $id
     *
     * @return \ilBiblFieldInterface
     */
    public function findById(int $id) : ilBiblFieldInterface;


    /**
     * @param \ilBiblAttributeInterface $attribute
     *
     * @return \ilBiblFieldInterface
     */
    public function findOrCreateFieldOfAttribute(ilBiblAttributeInterface $attribute) : ilBiblFieldInterface;


    /**
     * @param ilBiblFieldInterface $field
     *
     * @return int new position
     */
    public function forcePosition(ilBiblFieldInterface $field) : int;
}
