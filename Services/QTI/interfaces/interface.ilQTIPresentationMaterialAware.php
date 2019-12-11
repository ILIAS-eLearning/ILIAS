<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIPresentationMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIPresentationMaterialAware
{
    /**
     * @param ilQTIPresentationMaterial $flow_mat
     */
    public function setPresentationMaterial(ilQTIPresentationMaterial $presentation_material);

    /**
     * @return ilQTIPresentationMaterial|null
     */
    public function getPresentationMaterial();
}
