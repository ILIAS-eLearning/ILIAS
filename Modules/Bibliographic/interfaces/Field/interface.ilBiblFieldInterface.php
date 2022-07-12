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
 * Interface ilBiblEntryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldInterface
{
    public const DATA_TYPE_RIS = 1;
    public const DATA_TYPE_BIBTEX = 2;
    
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    public function getIdentifier() : string;
    
    public function setIdentifier(string $identifier) : void;
    
    public function getPosition() : ?int;
    
    public function setPosition(int $position) : void;
    
    public function isStandardField() : bool;
    
    public function setIsStandardField(bool $is_standard_field) : void;
    
    public function getDataType() : int;
    
    public function setDataType(int $data_type) : void;
    
    public function store() : void;
}
