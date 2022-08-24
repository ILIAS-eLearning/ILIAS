<?php

declare(strict_types=0);
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
 * Class ilLOXmlWriter
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOXmlWriter
{
    public const TYPE_TST_PO = 1;
    public const TYPE_TST_ALL = 2;
    public const TYPE_TST_RND = 3;

    private int $ref_id = 0;
    private int $obj_id = 0;
    private ilXmlWriter $writer;

    private ilLogger $log;
    protected ilSetting $setting;

    /**
     * Constructor
     */
    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjectId($a_ref_id);
        $this->writer = new ilXmlWriter();
        $this->setting = $DIC->settings();
        $this->log = $DIC->logger()->crs();
    }

    protected function getWriter(): ilXmlWriter
    {
        return $this->writer;
    }

    /**
     * Write xml
     */
    public function write(): void
    {
        $this->getWriter()->xmlStartTag('Objectives');

        // export settings
        $settings = ilLOSettings::getInstanceByObjId($this->obj_id);
        $settings->toXml($this->getWriter());

        $factory = new ilObjectFactory();
        $course = $factory->getInstanceByRefId($this->ref_id, false);
        if (!$course instanceof ilObjCourse) {
            $this->log->warning('Cannot create course instance for ref_id: ' . $this->ref_id);
            return;
        }
        $this->log->debug('Writing objective xml');
        foreach (ilCourseObjective::_getObjectiveIds($this->obj_id) as $objective_id) {
            $this->log->debug('Handling objective_id: ' . $objective_id);
            $objective = new ilCourseObjective($course, $objective_id);
            $objective->toXml($this->getWriter());
        }

        $this->getWriter()->xmlEndTag('Objectives');
    }

    public function getXml(): string
    {
        return $this->getWriter()->xmlDumpMem(false);
    }
}
