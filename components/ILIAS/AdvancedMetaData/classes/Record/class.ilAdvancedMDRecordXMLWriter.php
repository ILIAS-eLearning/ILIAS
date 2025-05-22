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

declare(strict_types=1);
/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordXMLWriter extends ilXmlWriter
{
    protected array $record_ids = [];
    protected ilSetting $settings;

    /**
     * Constructor
     * @access public
     * @param
     */
    public function __construct(array $a_record_ids)
    {
        global $DIC;

        parent::__construct();
        $this->settings = $DIC->settings();
        $this->record_ids = $a_record_ids;
    }

    public function write(): void
    {
        $this->buildHeader();
        $this->xmlStartTag('AdvancedMetaDataRecords');
        foreach ($this->record_ids as $record_id) {
            $record_obj = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            $record_obj->toXML($this);
        }
        $this->xmlEndTag('AdvancedMetaDataRecords');
    }

    /**
     * build header
     * @access protected
     */
    protected function buildHeader(): void
    {
        $this->xmlSetGenCmt("Export of ILIAS Advanced meta data records of installation " . $this->settings->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
