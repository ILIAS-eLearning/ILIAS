<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class for container reference export
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceXmlWriter
 *
 * @ingroup components\ILIASGroupReference
 */
class ilGroupReferenceXmlWriter extends ilContainerReferenceXmlWriter
{
    /**
     * ilGroupReferenceXmlWriter constructor.
     * @param ilObjGroupReference|null $ref
     */
    public function __construct(ilObjGroupReference $ref = null)
    {
        parent::__construct($ref);
    }

    protected function buildHeader(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
