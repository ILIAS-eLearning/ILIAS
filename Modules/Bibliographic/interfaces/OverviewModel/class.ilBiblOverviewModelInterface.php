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
 * Class ilBiblOverviewModelInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
interface ilBiblOverviewModelInterface
{
    public function getOvmId() : ?int;
    
    public function setOvmId(int $ovm_id) : void;
    
    public function getFileTypeId() : int;
    
    public function setFileTypeId(int $file_type) : void;
    
    public function getLiteratureType() : string;
    
    public function setLiteratureType(string $literature_type) : void;
    
    public function getPattern() : string;
    
    public function setPattern(string $pattern) : void;
}
