<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIPresentationMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIPresentationMaterialAware
{
    public function setPresentationMaterial(ilQTIPresentationMaterial $presentation_material) : void;

    public function getPresentationMaterial() : ?ilQTIPresentationMaterial;
}
