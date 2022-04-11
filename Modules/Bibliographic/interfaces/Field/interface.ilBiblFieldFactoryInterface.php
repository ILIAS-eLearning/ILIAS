<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Interface ilBiblFieldFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldFactoryInterface
{

    /**
     * @param int    $type     MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
     *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
     * @param string $identifier
     *
     * @return \ilBiblFieldInterface
     * @throws \ilException if a wrong $type is passed or field is not found
     *
     */
    public function getFieldByTypeAndIdentifier(int $type, string $identifier) : ilBiblFieldInterface;


    /**
     * @param int    $type     MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
     *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
     * @param string $identifier
     *
     * @return \ilBiblFieldInterface
     * @throws \ilException if a wrong $type is passed
     *
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
