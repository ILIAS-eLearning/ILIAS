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
    protected $flow_mat = array();
    
    /**
     * {@inheritdoc}
     */
    public function addFlowMat(ilQTIFlowMat $flow_mat)
    {
        $this->flow_mat[] = $flow_mat;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlowMat($index)
    {
        if (isset($this->flow_mat[$index])) {
            return $this->flow_mat[$index];
        }

        return null;
    }
}
