<?php declare(strict_types=1);

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
 * Interface ilDBStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBStatement
{

    /**
     * @param $fetch_mode int Is either ilDBConstants::FETCHMODE_ASSOC OR ilDBConstants::FETCHMODE_OBJECT
     * @return mixed Returns an array in fetchmode assoc and an object in fetchmode object.
     */
    public function fetchRow(int $fetch_mode);


    /**
     * @return mixed
     */
    public function fetch(int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC);


    public function rowCount() : int;


    public function numRows() : int;


    public function fetchObject() : ?stdClass;


    public function fetchAssoc() : ?array;


    public function execute(array $a_data = null) : ilDBStatement;
}
