<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIFlowMatAware
{
    public function addFlowMat(ilQTIFlowMat $flow_mat) : void;

    public function getFlowMat(int $index) : ?ilQTIFlowMat;
}
