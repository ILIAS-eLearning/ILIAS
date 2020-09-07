<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';
require_once 'Modules/Test/classes/class.ilTestExportFilename.php';

/**
 * Abstract parent class for all event hook plugin classes.
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
abstract class ilTestExportPlugin extends ilPlugin
{
    /**
     * @var ilObjTest
     */
    protected $test;

    /**
     * @var int
     */
    protected $timestmap = -1;
    
    /**
     * @var array
     */
    protected static $reserved_formats = array(
        'xml',
        'csv'
    );
    
    /**
     * Get Component Type
     * @return        string        Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_MODULE;
    }

    /**
     * Get Component Name.
     * @return        string        Component Name
     */
    final public function getComponentName()
    {
        return "Test";
    }

    /**
     * Get Slot Name.
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return "Export";
    }

    /**
     * Get Slot ID.
     * @return        string        Slot Id
     */
    final public function getSlotId()
    {
        return "texp";
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
    }

    /**
     * @param ilObjTest $test
     */
    final public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     * @return ilObjTest
     */
    final protected function getTest()
    {
        return $this->test;
    }

    /**
     * @param int $timestmap
     */
    public function setTimestmap($timestmap)
    {
        $this->timestmap = $timestmap;
    }

    /**
     * @return int
     */
    public function getTimestmap()
    {
        return $this->timestmap;
    }
    
    /**
     * @return string
     * @throws ilException
     */
    final public function getFormat()
    {
        $format_id = $this->getFormatIdentifier();

        if (!is_string($format_id)) {
            throw new ilException('The format must be of type string.');
        }

        if (!strlen($format_id)) {
            throw new ilException('The format is empty.');
        }

        if (strtolower($format_id) != $format_id) {
            throw new ilException('Please use a lowercase format.');
        }

        if (in_array($format_id, self::$reserved_formats)) {
            throw new ilException('The format must not be one of: ' . implode(', ', self::$reserved_formats));
        }

        return $format_id;
    }

    /**
     * @throws ilException
     */
    final public function export()
    {
        /**
         * @var $lng;
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$this->getTest() instanceof ilObjTest) {
            throw new ilException('Incomplete object configuration. Please pass an instance of ilObjTest before calling the export!');
        }

        try {
            $this->buildExportFile(new ilTestExportFilename($this->getTest()));
        } catch (ilException $e) {
            if ($this->txt($e->getMessage()) == '-' . $e->getMessage() . '-') {
                ilUtil::sendFailure($e->getMessage(), true);
            } else {
                ilUtil::sendFailure($this->txt($e->getMessage()), true);
            }
            $ilCtrl->redirectByClass('iltestexportgui');
        }

        ilUtil::sendSuccess($lng->txt('exp_file_created'), true);
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    /**
     * This method is called if the user wants to export a test of YOUR export type
     * If you throw an exception of type ilException with a respective language variable, ILIAS presents a translated failure message.
     * @throws ilException
     * @param string $export_path The path to store the export file
     */
    abstract protected function buildExportFile(ilTestExportFilename $export_path);

    /**
     * A unique identifier which describes your export type, e.g. imsm
     * There is currently no mapping implemented concerning the filename.
     * Feel free to create csv, xml, zip files ....
     *
     * @return string
     */
    abstract protected function getFormatIdentifier();

    /**
     * This method should return a human readable label for your export
     * @return string
     */
    abstract public function getFormatLabel();
}
