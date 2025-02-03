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
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCourseReferenceXmlWriter extends ilContainerReferenceXmlWriter
{
    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct(?ilObjCourseReference $ref = null)
    {
        parent::__construct($ref);
    }


    /**
     * Start writing xml
     */
    public function export(bool $a_with_header = true): void
    {
        if ($this->getMode() == self::MODE_EXPORT) {
            if ($a_with_header) {
                $this->buildHeader();
            }
            $this->buildReference();
            $this->buildTarget();
            $this->buildTitle();
            $this->buildCourseSettings();
            $this->buildFooter();
        }
    }

    /**
     * Add member update setting
     */
    protected function buildCourseSettings()
    {
        $this->xmlElement('MemberUpdate', [], $this->getReference()->isMemberUpdateEnabled() ? 1 : 0);
    }


    /**
     * Build xml header
     * @return void
     *@global <type> $ilSetting
     */
    protected function buildHeader(): void
    {
        global $ilSetting;

        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
