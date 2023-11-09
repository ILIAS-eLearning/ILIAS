<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* group reference xml importer
*
* @author Fabian Wolf <wolf@leifos.com>
* @extends ilContainerReferenceImporter
* @ingroup components\ILIASGroupReference
*/
class ilGroupReferenceImporter extends ilContainerReferenceImporter
{
    protected function getType(): string
    {
        return 'grpr';
    }

    protected function initParser(string $a_xml): ilContainerReferenceXmlParser
    {
        return new ilGroupReferenceXmlParser($a_xml);
    }
}
