<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/QTI/interfaces/interface.ilQTIFlowMatAware.php';

/**
 * Class ilQTIPresentationMaterial
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilQTIPresentationMaterial implements ilQTIFlowMatAware
{
    /**
     * @var ilQTIFlowMat[]
     */
    protected array $flow_mat = [];
    
    public function addFlowMat(ilQTIFlowMat $flow_mat) : void
    {
        $this->flow_mat[] = $flow_mat;
    }

    public function getFlowMat(int $index) : ?ilQTIFlowMat
    {
        return $this->flow_mat[$index] ?? null;
    }
}
