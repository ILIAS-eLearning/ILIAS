<?php
/**
 * Class ilAtomQueryTestHelper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAtomQueryTestHelper
{

    /**
     * @param \ilDBInterface $ilDB
     */
    public function __invoke(ilDBInterface $ilDB)
    {
        $ilDB->listTables();
    }
}
