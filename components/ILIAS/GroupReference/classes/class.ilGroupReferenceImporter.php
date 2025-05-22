<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
