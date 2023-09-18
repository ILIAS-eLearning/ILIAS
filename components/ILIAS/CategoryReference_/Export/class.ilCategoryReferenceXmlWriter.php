<?php

declare(strict_types=1);

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
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCategoryReferenceXmlWriter extends ilContainerReferenceXmlWriter
{
    public function __construct(ilObjCategoryReference $ref = null)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct($ref);
    }

    protected function buildHeader(): void
    {
        $ilSetting = $this->settings;

        $this->xmlSetDtdDef("<!DOCTYPE category reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_category_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS category reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
