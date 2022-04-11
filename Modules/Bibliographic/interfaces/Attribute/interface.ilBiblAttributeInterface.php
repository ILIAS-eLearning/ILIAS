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
 * Interface ilBiblAttributeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblAttributeInterface
{
    public function getEntryId() : int;
    
    public function setEntryId(int $entry_id) : void;
    
    public function getName() : string;
    
    public function setName(string $name) : void;
    
    public function getValue() : string;
    
    public function setValue(string $value) : void;
    
    public function getId() : ?int;
    
    public function setId(int $id) : void;
}
