<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIMaterialAware
{
    public function addMaterial(ilQTIMaterial $material) : void;

    public function getMaterial(int $index) : ?ilQTIMaterial;
}
