<?php declare(strict_types=1);

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
 * Class ilLinkResourceItems
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesWebResource
 */
class ilLinkResourceItems
{
    protected ilDBInterface $db;

    private int $id = 0;
    private string $title = '';
    private string $description = '';
    private string $target = '';
    private bool $status = false;
    private bool $check = false;
    private int $c_date = 0;
    private int $m_date = 0;
    private int $last_check = 0;
    private bool $valid = false;
    private bool $internal = false;

    private int $webr_ref_id;
    private int $webr_id;

    /**
     * Constructor
     * @access public
     */
    public function __construct(int $webr_id)
    {
        global $DIC;

        $this->webr_ref_id = 0;
        $this->webr_id = $webr_id;
        $this->db = $DIC->database();
    }

    public static function lookupItem(int $a_webr_id, int $a_link_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_link_id, 'integer');

        $res = $ilDB->query($query);
        $item = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = (string) $row->title;
            $item['description'] = (string) $row->description;
            $item['target'] = (string) $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = (bool) $row->disable_check;
            $item['create_date'] = (int) $row->create_date;
            $item['last_update'] = (int) $row->last_update;
            $item['last_check'] = (int) $row->last_check;
            $item['valid'] = (bool) $row->valid;
            $item['link_id'] = (int) $row->link_id;
            $item['internal'] = (int) $row->internal;
        }
        return $item;
    }

    public static function updateTitle(int $a_link_id, string $a_title) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'UPDATE webr_items SET ' .
            'title = ' . $ilDB->quote($a_title, 'text') . ' ' .
            'WHERE link_id = ' . $ilDB->quote($a_link_id, 'integer');
        $ilDB->manipulate($query);
    }

    // SET GET
    public function setLinkResourceRefId(int $a_ref_id) : void
    {
        $this->webr_ref_id = $a_ref_id;
    }

    public function getLinkResourceRefId() : int
    {
        return $this->webr_ref_id;
    }

    public function setLinkResourceId(int $a_id) : void
    {
        $this->webr_id = $a_id;
    }

    public function getLinkResourceId() : int
    {
        return $this->webr_id;
    }

    public function setLinkId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getLinkId() : int
    {
        return $this->id;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setTarget(string $a_target) : void
    {
        $this->target = $a_target;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function setActiveStatus(bool $a_status) : void
    {
        $this->status = $a_status;
    }

    public function getActiveStatus() : bool
    {
        return $this->status;
    }

    public function setDisableCheckStatus(bool $a_status) : void
    {
        $this->check = $a_status;
    }

    public function getDisableCheckStatus() : bool
    {
        return $this->check;
    }

    // PRIVATE
    private function setCreateDate(int $a_date) : void
    {
        $this->c_date = $a_date;
    }

    public function getCreateDate() : int
    {
        return $this->c_date;
    }

    // PRIVATE
    private function setLastUpdateDate(int $a_date) : void
    {
        $this->m_date = $a_date;
    }

    public function getLastUpdateDate() : int
    {
        return $this->m_date;
    }

    public function setLastCheckDate(int $a_date) : void
    {
        $this->last_check = $a_date;
    }

    public function getLastCheckDate() : int
    {
        return $this->last_check;
    }

    public function setValidStatus(bool $a_status) : void
    {
        $this->valid = $a_status;
    }

    public function getValidStatus() : bool
    {
        return $this->valid;
    }

    public function setInternal(bool $a_status) : void
    {
        $this->internal = $a_status;
    }

    public function getInternal() : bool
    {
        return $this->internal;
    }

    /**
     * Copy web resource items
     */
    public function cloneItems(int $a_new_id) : bool
    {
        $appender = new ilParameterAppender($this->getLinkResourceId());

        foreach ($this->getAllItems() as $item) {
            $new_item = new ilLinkResourceItems($a_new_id);
            $new_item->setTitle($item['title']);
            $new_item->setDescription($item['description']);
            $new_item->setTarget($item['target']);
            $new_item->setActiveStatus($item['active']);
            $new_item->setDisableCheckStatus($item['disable_check']);
            $new_item->setLastCheckDate($item['last_check']);
            $new_item->setValidStatus($item['valid']);
            $new_item->setInternal($item['internal']);
            $new_item->add(true);

            // Add parameters
            foreach (ilParameterAppender::_getParams(
                $item['link_id']
            ) as $data) {
                $appender->setName($data['name']);
                $appender->setValue($data['value']);
                $appender->add($new_item->getLinkId());
            }
            unset($new_item);
        }
        return true;
    }

    public function delete(int $a_item_id, bool $a_update_history = true) : bool
    {
        $item = $this->getItem($a_item_id);
        $query = "DELETE FROM webr_items " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getLinkResourceId(),
                'integer'
            ) . " " .
            "AND link_id = " . $this->db->quote($a_item_id, 'integer');
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "delete",
                [$item['title']]
            );
        }
        return true;
    }

    public function update(bool $a_update_history = true) : bool
    {
        if (!$this->getLinkId()) {
            return false;
        }
        $this->setLastUpdateDate(time());
        $query = "UPDATE webr_items " .
            "SET title = " . $this->db->quote(
                $this->getTitle(),
                'text'
            ) . ", " .
            "description = " . $this->db->quote(
                $this->getDescription(),
                'text'
            ) . ", " .
            "target = " . $this->db->quote($this->getTarget(), 'text') . ", " .
            "active = " . $this->db->quote(
                $this->getActiveStatus(),
                'integer'
            ) . ", " .
            "valid = " . $this->db->quote(
                $this->getValidStatus(),
                'integer'
            ) . ", " .
            "disable_check = " . $this->db->quote(
                $this->getDisableCheckStatus(),
                'integer'
            ) . ", " .
            "internal = " . $this->db->quote(
                $this->getInternal(),
                'integer'
            ) . ", " .
            "last_update = " . $this->db->quote(
                $this->getLastUpdateDate(),
                'integer'
            ) . ", " .
            "last_check = " . $this->db->quote(
                $this->getLastCheckDate(),
                'integer'
            ) . " " .
            "WHERE link_id = " . $this->db->quote(
                $this->getLinkId(),
                'integer'
            ) . " " .
            "AND webr_id = " . $this->db->quote(
                $this->getLinkResourceId(),
                'integer'
            );
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "update",
                [$this->getTitle()]
            );
        }

        return true;
    }

    public function updateValid(bool $a_status) : bool
    {
        $query = "UPDATE webr_items " .
            "SET valid = " . $this->db->quote($a_status, 'integer') . " " .
            "WHERE link_id = " . $this->db->quote(
                $this->getLinkId(),
                'integer'
            );
        $res = $this->db->manipulate($query);

        return true;
    }

    public function updateActive(bool $a_status) : bool
    {
        $query = "UPDATE webr_items " .
            "SET active = " . $this->db->quote($a_status, 'integer') . " " .
            "WHERE link_id = " . $this->db->quote(
                $this->getLinkId(),
                'integer'
            );

        $this->db->query($query);

        return true;
    }

    public function updateDisableCheck(bool $a_status) : bool
    {
        $query = "UPDATE webr_items " .
            "SET disable_check = " . $this->db->quote(
                $a_status,
                'integer'
            ) . " " .
            "WHERE link_id = " . $this->db->quote(
                $this->getLinkId(),
                'integer'
            );
        $res = $this->db->manipulate($query);

        return true;
    }

    public function updateLastCheck(int $a_offset = 0) : bool
    {
        if ($a_offset !== 0) {
            $period = $a_offset ?: 0;
            $time = time() - $period;

            $query = "UPDATE webr_items " .
                "SET last_check = " . $this->db->quote(
                    time(),
                    'integer'
                ) . " " .
                "WHERE webr_id = " . $this->db->quote(
                    $this->getLinkResourceId(),
                    'integer'
                ) . " " .
                "AND disable_check = '0' " .
                "AND last_check < " . $this->db->quote($time, 'integer');
        } else {
            $query = "UPDATE webr_items " .
                "SET last_check = " . $this->db->quote(
                    time(),
                    'integer'
                ) . " " .
                "WHERE webr_id = " . $this->db->quote(
                    $this->getLinkResourceId(),
                    'integer'
                ) . " " .
                "AND disable_check = '0' ";
        }
        $res = $this->db->manipulate($query);
        return true;
    }

    public function updateValidByCheck(int $a_offset = 0) : bool
    {
        if ($a_offset !== 0) {
            $period = $a_offset ?: 0;
            $time = time() - $period;

            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $this->db->quote(
                    $this->getLinkResourceId(),
                    'integer'
                ) . " " .
                "AND last_check < " . $this->db->quote($time, 'integer');
        } else {
            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $this->db->quote(
                    $this->getLinkResourceId(),
                    'integer'
                );
        }
        $res = $this->db->manipulate($query);
        return true;
    }

    public function add(bool $a_update_history = true) : int
    {
        $this->setLastUpdateDate(time());
        $this->setCreateDate(time());

        $next_id = $this->db->nextId('webr_items');
        $query = "INSERT INTO webr_items (link_id,title,description,target,active,disable_check," .
            "last_update,create_date,webr_id,valid,internal) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getTitle(), 'text') . ", " .
            $this->db->quote($this->getDescription(), 'text') . ", " .
            $this->db->quote($this->getTarget(), 'text') . ", " .
            $this->db->quote($this->getActiveStatus(), 'integer') . ", " .
            $this->db->quote($this->getDisableCheckStatus(), 'integer') . ", " .
            $this->db->quote($this->getLastUpdateDate(), 'integer') . ", " .
            $this->db->quote($this->getCreateDate(), 'integer') . ", " .
            $this->db->quote($this->getLinkResourceId(), 'integer') . ", " .
            $this->db->quote($this->getValidStatus(), 'integer') . ', ' .
            $this->db->quote($this->getInternal(), 'integer') . ' ' .
            ")";
        $res = $this->db->manipulate($query);

        $link_id = $next_id;
        $this->setLinkId($link_id);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "add",
                [$this->getTitle()]
            );
        }
        return $link_id;
    }

    public function readItem(int $a_link_id) : bool
    {
        $query = "SELECT * FROM webr_items " .
            "WHERE link_id = " . $this->db->quote($a_link_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle((string) $row->title);
            $this->setDescription((string) $row->description);
            $this->setTarget((string) $row->target);
            $this->setActiveStatus((bool) $row->active);
            $this->setDisableCheckStatus((bool) $row->disable_check);
            $this->setCreateDate((int) $row->create_date);
            $this->setLastUpdateDate((int) $row->last_update);
            $this->setLastCheckDate((int) $row->last_check);
            $this->setValidStatus((bool) $row->valid);
            $this->setLinkId((int) $row->link_id);
            $this->setInternal((bool) $row->internal);
        }
        return true;
    }

    public function getItem(int $a_link_id) : array
    {
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getLinkResourceId(),
                'integer'
            ) . " " .
            "AND link_id = " . $this->db->quote($a_link_id, 'integer');

        $res = $this->db->query($query);
        $item = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = (string) $row->title;
            $item['description'] = (string) $row->description;
            $item['target'] = (string) $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = (bool) $row->disable_check;
            $item['create_date'] = (int) $row->create_date;
            $item['last_update'] = (int) $row->last_update;
            $item['last_check'] = (int) $row->last_check;
            $item['valid'] = (bool) $row->valid;
            $item['link_id'] = (int) $row->link_id;
            $item['internal'] = (bool) $row->internal;
        }
        return $item;
    }

    /**
     * @return int[]
     */
    public static function getAllItemIds(int $a_webr_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT link_id FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->query($query);
        $link_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $link_ids[] = (int) $row['link_id'];
        }
        return $link_ids;
    }
    
    public function getAllItems() : array
    {
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getLinkResourceId(),
                'integer'
            );

        $res = $this->db->query($query);
        $items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[$row->link_id]['title'] = (string) $row->title;
            $items[$row->link_id]['description'] = (string) $row->description;
            $items[$row->link_id]['target'] = (string) $row->target;
            $items[$row->link_id]['active'] = (bool) $row->active;
            $items[$row->link_id]['disable_check'] = (bool) $row->disable_check;
            $items[$row->link_id]['create_date'] = (int) $row->create_date;
            $items[$row->link_id]['last_update'] = (int) $row->last_update;
            $items[$row->link_id]['last_check'] = (int) $row->last_check;
            $items[$row->link_id]['valid'] = (bool) $row->valid;
            $items[$row->link_id]['link_id'] = (int) $row->link_id;
            $items[$row->link_id]['internal'] = (bool) $row->internal;
        }
        return $items;
    }

    /**
     * Sort items (sorting mode depends on sorting setting)
     */
    public function sortItems(array $a_items) : array
    {
        $mode = ilContainerSortingSettings::_lookupSortMode(
            $this->getLinkResourceId()
        );

        if ($mode == ilContainer::SORT_TITLE) {
            return ilArrayUtil::sortArray(
                $a_items,
                'title',
                'asc',
                false,
                true
            );
        }

        $sorted = $unsorted = [];
        if ($mode == ilContainer::SORT_MANUAL) {
            $pos = ilContainerSorting::lookupPositions(
                $this->getLinkResourceId()
            );
            foreach ($a_items as $link_id => $item) {
                if (isset($pos[$link_id])) {
                    $sorted[$link_id] = $item;
                    $sorted[$link_id]['position'] = $pos[$link_id];
                } else {
                    $unsorted[$link_id] = $item;
                }
            }
            $sorted = ilArrayUtil::sortArray(
                $sorted,
                'position',
                'asc',
                true,
                true
            );
            $unsorted = ilArrayUtil::sortArray(
                $unsorted,
                'title',
                'asc',
                false,
                true
            );
            return $sorted + $unsorted;
        }
        return $a_items;
    }

    public function getActivatedItems() : array
    {
        $active_items = [];
        foreach ($this->getAllItems() as $id => $item_data) {
            if ($item_data['active']) {
                $active_items[$id] = $item_data;
            }
        }
        return $active_items;
    }

    public function getCheckItems(int $a_offset = 0) : array
    {
        $period = $a_offset ?: 0;
        $time = time() - $period;

        $check_items = [];
        foreach ($this->getAllItems() as $id => $item_data) {
            if (!$item_data['disable_check'] && (!$item_data['last_check'] || $item_data['last_check'] < $time)) {
                $check_items[$id] = $item_data;
            }
        }
        return $check_items;
    }

    public static function _deleteAll(int $webr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->manipulate(
            "DELETE FROM webr_items WHERE webr_id = " . $ilDB->quote(
                $webr_id,
                'integer'
            )
        );
        return true;
    }

    /**
     * Check whether there is only one active link in the web resource.
     * In this case this link is shown in a new browser window
     */
    public static function _isSingular(int $a_webr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . ' ' .
            "AND active = " . $ilDB->quote(1, 'integer') . ' ';
        $res = $ilDB->query($query);
        return $res->numRows() == 1;
    }

    /**
     * Get number of assigned links
     */
    public static function lookupNumberOfLinks(int $a_webr_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT COUNT(*) num FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) $row->num;
    }

    /**
     * Get first link item
     * Check before with _isSingular() if there is more or less than one
     */
    public static function _getFirstLink(int $a_webr_id) : array
    {
        return ilObjLinkResourceAccess::_getFirstLink($a_webr_id);
    }

    public function validate() : bool
    {
        return $this->getTarget() && $this->getTitle();
    }

    /**
     * Write link XML
     */
    public function toXML(ilXmlWriter $writer) : void
    {
        $items = $this->sortItems($this->getAllItems());

        $position = 0;
        foreach (array_keys($items) as $item_id) {
            ++$position;
            $link = self::lookupItem($this->getLinkResourceId(), $item_id);

            $writer->xmlStartTag(
                'WebLink',
                array(
                    'id' => $link['link_id'],
                    'active' => $link['active'] ? 1 : 0,
                    'valid' => $link['valid'] ? 1 : 0,
                    'disableValidation' => $link['disable_check'] ? 1 : 0,
                    'position' => $position,
                    'internal' => $link['internal']
                )
            );
            $writer->xmlElement('Title', array(), $link['title']);
            $writer->xmlElement('Description', array(), $link['description']);
            $writer->xmlElement('Target', array(), $link['target']);

            // Dynamic parameters
            foreach (ilParameterAppender::_getParams(
                $link['link_id']
            ) as $param_id => $param) {
                $value = '';
                switch ($param['value']) {
                    case ilParameterAppender::LINKS_USER_ID:
                        $value = 'userId';
                        break;

                    case ilParameterAppender::LINKS_LOGIN:
                        $value = 'userName';
                        break;

                    case ilParameterAppender::LINKS_MATRICULATION:
                        $value = 'matriculation';
                        break;
                }

                if (!$value) {
                    // Fix for deprecated LINKS_SESSION
                    continue;
                }

                $writer->xmlElement(
                    'DynamicParameter',
                    array(
                        'id' => $param_id,
                        'name' => $param['name'],
                        'type' => $value
                    )
                );
            }

            $writer->xmlEndTag('WebLink');
        }
    }
}
