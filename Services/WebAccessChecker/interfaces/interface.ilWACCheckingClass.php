<?php
// declare(strict_types=1);

require_once('./Services/Object/classes/class.ilObject2.php');

/**
 * Class ilWACCheckingClass
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
interface ilWACCheckingClass
{

    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath);
}
