<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIMaterialAware
{
    /**
     * @param ilQTIMaterial $flow_mat
     */
    public function addMaterial(ilQTIMaterial $material);

    /**
     * @param $index int
     * @return ilQTIMaterial|null
     */
    public function getMaterial($index);
}
