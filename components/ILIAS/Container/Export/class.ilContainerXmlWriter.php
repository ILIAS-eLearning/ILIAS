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
 * XML writer for container structure
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilContainerXmlWriter extends ilXmlWriter
{
    protected ilTree $tree;
    protected ?ilExportOptions $exp_options = null;
    private int $source = 0;
    protected ilObjectDefinition $objDefinition;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        parent::__construct();
        $this->source = $a_ref_id;
        $this->exp_options = ilExportOptions::getInstance();
        $this->objDefinition = $DIC['objDefinition'];
    }

    /**
     * Write XML
     * @throws UnexpectedValueException Thrown if obj_id is not of type webr or no obj_id is given
     */
    public function write(): void
    {
        $this->xmlStartTag('Items');
        $this->writeSubitems($this->source);
        $this->xmlEndTag('Items');
    }

    /**
     * Write tree childs
     * Recursive method
     */
    protected function writeSubitems(int $a_ref_id): void
    {
        $tree = $this->tree;

        // because of the co-page-stuff (incl. styles) we also need to process the container itself
        if ($a_ref_id != $this->source) {
            $mode = $this->exp_options->getOptionByRefId($a_ref_id, ilExportOptions::KEY_ITEM_MODE);
            if ($mode == null || $mode == ilExportOptions::EXPORT_OMIT) {
                return;
            }
        }

        $obj_id = ilObject::_lookupObjId($a_ref_id);

        $attrs = [];
        $attrs['RefId'] = $a_ref_id;
        $attrs['Id'] = $obj_id;
        $attrs['Title'] = ilObject::_lookupTitle($obj_id);
        $attrs['Type'] = ilObject::_lookupType($obj_id);
        $attrs['Page'] = ilContainerPage::_exists('cont', $obj_id);
        $attrs['StartPage'] = ilContainerStartObjectsPage::_exists('cstr', $obj_id);
        $attrs['Style'] = ilObjStyleSheet::lookupObjectStyle($obj_id);
        if ($this->objDefinition->supportsOfflineHandling($attrs['Type'])) {
            $attrs['Offline'] = ilObject::lookupOfflineStatus($obj_id) ? '1' : '0';
        }
        $this->xmlStartTag(
            'Item',
            $attrs
        );
        $this->writeCourseItemInformation($a_ref_id);

        foreach ($tree->getChilds($a_ref_id) as $node) {
            $this->writeSubitems($node['child']);
        }

        $this->xmlEndTag('Item');
    }


    /**
     * Write course item information
     * Starting time, ending time...
     */
    protected function writeCourseItemInformation(int $a_ref_id): void
    {
        $item = ilObjectActivation::getItem($a_ref_id);

        $this->xmlStartTag(
            'Timing',
            [
                'Type' => $item['timing_type'],
                'Visible' => $item['visible'],
                'Changeable' => $item['changeable'],
            ]
        );
        if ($item['timing_start']) {
            $tmp_date = new ilDateTime($item['timing_start'], IL_CAL_UNIX);
            $this->xmlElement('Start', [], $tmp_date->get(IL_CAL_DATETIME, '', ilTimeZone::UTC));
        }
        if ($item['timing_end']) {
            $tmp_date = new ilDateTime($item['timing_end'], IL_CAL_UNIX);
            $this->xmlElement('End', [], $tmp_date->get(IL_CAL_DATETIME, '', ilTimeZone::UTC));
        }
        if ($item['suggestion_start']) {
            $tmp_date = new ilDateTime($item['suggestion_start'], IL_CAL_UNIX);
            $this->xmlElement('SuggestionStart', [], $tmp_date->get(IL_CAL_DATETIME, '', ilTimeZone::UTC));
        }
        if ($item['suggestion_end']) {
            $tmp_date = new ilDateTime($item['suggestion_end'], IL_CAL_UNIX);
            $this->xmlElement('SuggestionEnd', [], $tmp_date->get(IL_CAL_DATETIME, '', ilTimeZone::UTC));
        }
        if ($item['earliest_start'] ?? false) {
            $tmp_date = new ilDateTime($item['earliest_start'], IL_CAL_UNIX);
            $this->xmlElement('EarliestStart', [], $tmp_date->get(IL_CAL_DATETIME, '', ilTimeZone::UTC));
        }

        $this->xmlEndTag('Timing');
    }

    protected function buildHeader(): void
    {
        $this->xmlSetGenCmt("Container object");
        $this->xmlHeader();
    }
}
