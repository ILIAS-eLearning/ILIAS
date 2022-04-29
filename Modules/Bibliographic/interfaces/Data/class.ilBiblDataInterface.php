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
 * Class ilBiblDataInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
interface ilBiblDataInterface
{
    public function getId() : ?int;
    
    public function setId(int $id) : void;
    
    /**
     * @deprecated
     */
    public function getFilename() : ?string;
    
    /**
     * @deprecated
     */
    public function setFilename(string $filename) : void;
    
    public function isOnline() : bool;
    
    public function setIsOnline(int $is_online) : void;
    
    public function getFileType() : int;
    
    public function setFileType(int $file_type) : void;
}
