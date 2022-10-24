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
    public const CDATE_OPERATOR_BEFORE = 1;
    public const CDATE_OPERATOR_AFTER = 2;
    public const CDATE_OPERATOR_ON = 3;

    private ?int $cdate_operator = null;
    private ?ilDate $cdate_date = null;


    public function __construct(ilQueryParser $qp_obj)
    {
        parent::__construct($qp_obj);
        $this->setFields(array('title','description'));
    }




    public function performSearch(): ilSearchResult
    {
        $in = $this->__createInStatement();
        $where = $this->__createWhereCondition();



        $cdate = '';
        if ($this->getCreationDateFilterDate() instanceof ilDate) {
            if ($this->getCreationDateFilterOperator()) {
                switch ($this->getCreationDateFilterOperator()) {
                    case self::CDATE_OPERATOR_AFTER:
                        $cdate = 'AND create_date >= ' . $this->db->quote($this->getCreationDateFilterDate()->get(IL_CAL_DATE), 'text') . ' ';
                        break;

                    case self::CDATE_OPERATOR_BEFORE:
                        $cdate = 'AND create_date <= ' . $this->db->quote($this->getCreationDateFilterDate()->get(IL_CAL_DATE), 'text') . ' ';
                        break;

                    case self::CDATE_OPERATOR_ON:
                        $cdate = 'AND ' . $this->db->like(
                            'create_date',
                            'text',
                            $this->getCreationDateFilterDate()->get(IL_CAL_DATE) . '%'
                        );
                        break;
                }
            }
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


    public function setCreationDateFilterDate(ilDate $day): void
    {
        $this->cdate_date = $day;
    }

    public function setCreationDateFilterOperator(int $a_operator): void
    {
        $this->cdate_operator = $a_operator;
    }

    public function getCreationDateFilterDate(): ?ilDate
    {
        return $this->cdate_date;
    }

    public function getCreationDateFilterOperator(): ?int
    {
        return $this->cdate_operator;
    }
}
