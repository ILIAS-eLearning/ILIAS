<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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
