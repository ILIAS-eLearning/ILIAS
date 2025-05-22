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
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilObjectSearch extends ilAbstractSearch
{
    private ?ilDate $cdate_start_date = null;
    private ?ilDate $cdate_end_date = null;


    public function __construct(ilQueryParser $qp_obj)
    {
        parent::__construct($qp_obj);
        $this->setFields(array('title','description'));
    }


    public static function raiseContentChanged(int $obj_id): void
    {
        global $DIC;

        $DIC->event()->raise(
            'components/ILIAS/Search',
            'contentChanged',
            [
                "obj_id" => $obj_id
            ]
        );
    }

    public function performSearch(): ilSearchResult
    {
        $in = $this->__createInStatement();
        $where = $this->__createWhereCondition();



        $cdate = '';
        if ($this->getCreationDateFilterStartDate() && is_null($this->getCreationDateFilterEndDate())) {
            $cdate = 'AND create_date >= ' . $this->db->quote($this->getCreationDateFilterStartDate()->get(IL_CAL_DATE), 'text') . ' ';
        } elseif ($this->getCreationDateFilterEndDate() && is_null($this->getCreationDateFilterStartDate())) {
            $cdate = 'AND create_date <= ' . $this->db->quote($this->getCreationDateFilterEndDate()->get(IL_CAL_DATE), 'text') . ' ';
        } elseif ($this->getCreationDateFilterStartDate() && $this->getCreationDateFilterEndDate()) {
            $cdate = 'AND create_date >= ' . $this->db->quote($this->getCreationDateFilterStartDate()->get(IL_CAL_DATE), 'text') . ' ' .
                    'AND create_date <= ' . $this->db->quote($this->getCreationDateFilterEndDate()->get(IL_CAL_DATE), 'text') . ' ';
        }

        $locate = $this->__createLocateString();

        $query = "SELECT obj_id,type " .
            $locate .
            "FROM object_data " .
            $where . " " . $cdate . ' ' . $in . ' ' .
            "ORDER BY obj_id DESC";

        ilLoggerFactory::getLogger('src')->debug('Object search query: ' . $query);

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry((int) $row->obj_id, (string) $row->type, $this->__prepareFound($row));
        }
        return $this->search_result;
    }



    public function __createInStatement(): string
    {
        $in = ' AND ' . $this->db->in('type', (array) $this->object_types, false, 'text');
        if ($this->getIdFilter()) {
            $in .= ' AND ';
            $in .= $this->db->in('obj_id', $this->getIdFilter(), false, 'integer');
        }
        return $in;
    }


    public function setCreationDateFilterStartDate(?ilDate $day): void
    {
        $this->cdate_start_date = $day;
    }

    public function getCreationDateFilterStartDate(): ?ilDate
    {
        return $this->cdate_start_date;
    }

    public function setCreationDateFilterEndDate(?ilDate $day): void
    {
        $this->cdate_end_date = $day;
    }

    public function getCreationDateFilterEndDate(): ?ilDate
    {
        return $this->cdate_end_date;
    }
}
