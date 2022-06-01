<?php declare(strict_types=0);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * XML writer learning progress
 * @author  Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesTracking
 */
class ilLPXmlWriter extends ilXmlWriter
{
    private bool $add_header = true;
    private string $timestamp = "";
    private bool $include_ref_ids = false;
    private array $type_filter = array();

    protected ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct(bool $a_add_header)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->add_header = $a_add_header;
        parent::__construct();
    }

    /**
     * Set timestamp
     * @param string $a_val timestamp (YYYY-MM-DD hh:mm:ss)
     */
    public function setTimestamp(string $a_val) : void
    {
        $this->timestamp = $a_val;
    }

    /**
     * Get timestamp
     * @return string timestamp (YYYY-MM-DD hh:mm:ss)
     */
    public function getTimestamp() : string
    {
        return $this->timestamp;
    }

    public function setIncludeRefIds(bool $a_val) : void
    {
        $this->include_ref_ids = $a_val;
    }

    public function getIncludeRefIds() : bool
    {
        return $this->include_ref_ids;
    }

    /**
     * Set type filter
     * @param string[] $a_val
     */
    public function setTypeFilter(array $a_val) : void
    {
        $this->type_filter = $a_val;
    }

    /**
     * Get type filter
     * @return string[]
     */
    public function getTypeFilter() : array
    {
        return $this->type_filter;
    }

    /**
     * Write XML
     * @return
     * @throws UnexpectedValueException Thrown if obj_id is not of type webr or no obj_id is given
     */
    public function write() : void
    {
        $this->init();
        if ($this->add_header) {
            $this->buildHeader();
        }
        $this->addLPInformation();
    }

    protected function buildHeader() : void
    {
        $this->xmlHeader();
    }

    protected function init() : void
    {
        $this->xmlClear();
    }

    public function addLPInformation() : void
    {
        $this->xmlStartTag('LPData', array());
        $set = $this->db->query(
            $q = "SELECT * FROM ut_lp_marks " .
                " WHERE status_changed >= " . $this->db->quote(
                    $this->getTimestamp(),
                    "timestamp"
                )
        );

        while ($rec = $this->db->fetchAssoc($set)) {
            $ref_ids = array();
            if ($this->getIncludeRefIds()) {
                $ref_ids = ilObject::_getAllReferences((int) $rec["obj_id"]);
            }

            if (!is_array($this->getTypeFilter()) ||
                (count($this->getTypeFilter()) == 0) ||
                in_array(
                    ilObject::_lookupType((int) $rec["obj_id"]),
                    $this->getTypeFilter()
                )) {
                $this->xmlElement(
                    'LPChange',
                    array(
                        'UserId' => (int) $rec["usr_id"],
                        'ObjId' => (int) $rec["obj_id"],
                        'RefIds' => implode(",", $ref_ids),
                        'Timestamp' => $rec["status_changed"],
                        'LPStatus' => (int) $rec["status"]
                    )
                );
            }
        }
        $this->xmlEndTag('LPData');
    }
}
