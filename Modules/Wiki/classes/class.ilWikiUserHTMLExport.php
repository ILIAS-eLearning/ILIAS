<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Class manages user html export
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilWikiUserHTMLExport
{
    const PROCESS_OTHER_USER = 0;	// another user has started a running export
    const PROCESS_STARTED = 1;		// export has been started by current user
    const PROCESS_UPTODATE = 2;		// no export necessary, current export is up-to-date


    const NOT_RUNNING = 0;
    const RUNNING = 1;

    protected $data;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilObjWiki
     */
    protected $wiki;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Construct
     *
     * @param
     * @return
     */
    public function __construct(ilObjWiki $a_wiki, ilDBInterface $a_db, ilObjUser $a_user)
    {
        $this->db = $a_db;
        $this->wiki = $a_wiki;
        $this->user = $a_user;
        $this->read();
        $this->log = ilLoggerFactory::getLogger('wiki');
    }

    /**
     * Read
     *
     * @param
     * @return
     */
    protected function read()
    {
        $set = $this->db->query(
            "SELECT * FROM wiki_user_html_export " .
            " WHERE wiki_id  = " . $this->db->quote($this->wiki->getId(), "integer")
        );
        if (!$this->data = $this->db->fetchAssoc($set)) {
            $this->data = array();
        }
    }

    /**
     * Get process
     *
     * @param
     * @return
     */
    protected function getProcess()
    {
        $this->log->debug("getProcess");
        $last_change = ilPageObject::getLastChangeByParent("wpg", $this->wiki->getId());

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock('wiki_user_html_export');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($last_change, &$ret) {
            $this->log->debug("atom query start");
            
            $this->read();
            $ts = ilUtil::now();

            if ($this->data["start_ts"] != "" &&
                $this->data["start_ts"] > $last_change) {
                $ret = self::PROCESS_UPTODATE;
                $this->log->debug("return: " . self::PROCESS_UPTODATE);
                return;
            }

            if (!isset($this->data["wiki_id"])) {
                $this->log->debug("insert, wiki id: " . $this->wiki->getId() . ", user id: " . $this->user->getId() . ", ts: " . $ts);
                $ilDB->manipulate("INSERT INTO wiki_user_html_export  " .
                    "(wiki_id, usr_id, progress, start_ts, status) VALUES (" .
                    $ilDB->quote($this->wiki->getId(), "integer") . "," .
                    $ilDB->quote($this->user->getId(), "integer") . "," .
                    $ilDB->quote(0, "integer") . "," .
                    $ilDB->quote($ts, "timestamp") . "," .
                    $ilDB->quote(self::RUNNING, "integer") .
                    ")");
            } else {
                $this->log->debug("update, wiki id: " . $this->wiki->getId() . ", user id: " . $this->user->getId() . ", ts: " . $ts);
                $ilDB->manipulate(
                    "UPDATE wiki_user_html_export SET " .
                    " start_ts = " . $ilDB->quote($ts, "timestamp") . "," .
                    " usr_id = " . $ilDB->quote($this->user->getId(), "integer") . "," .
                    " progress = " . $ilDB->quote(0, "integer") . "," .
                    " status = " . $ilDB->quote(self::RUNNING, "integer") .
                    " WHERE status = " . $ilDB->quote(self::NOT_RUNNING, "integer") .
                    " AND wiki_id = " . $ilDB->quote($this->wiki->getId(), "integer")
                );
                $this->read();
            }

            if ($this->data["start_ts"] == $ts && $this->data["usr_id"] == $this->user->getId()) {
                //  we started the process
                $ret = self::PROCESS_STARTED;
                $this->log->debug("return: " . self::PROCESS_STARTED);
                return;
            }

            // process was already running
            $ret = self::PROCESS_OTHER_USER;
            $this->log->debug("return: " . self::PROCESS_OTHER_USER);
        });

        $ilAtomQuery->run();

        $this->log->debug("outer return: " . $ret);

        return $ret;
    }

    /**
     * Update status
     *
     * @param
     * @return
     */
    public function updateStatus($a_progress, $a_status)
    {
        $this->db->manipulate(
            "UPDATE wiki_user_html_export SET " .
            " progress = " . $this->db->quote((int) $a_progress, "integer") . "," .
            " status = " . $this->db->quote((int) $a_status, "integer") .
            " WHERE wiki_id = " . $this->db->quote($this->wiki->getId(), "integer") .
            " AND usr_id = " . $this->db->quote($this->user->getId(), "integer")
        );

        $this->read();
    }

    /**
     * Get Progress
     *
     * @param
     * @return
     */
    public function getProgress()
    {
        $set = $this->db->query(
            "SELECT progress, status FROM wiki_user_html_export " .
            " WHERE wiki_id = " . $this->db->quote($this->wiki->getId(), "integer")
        );
        $rec = $this->db->fetchAssoc($set);

        return array("progress" => (int) $rec["progress"], "status" => (int) $rec["status"]);
    }


    /**
     * Init user html export
     *
     * @param
     * @return
     */
    public function initUserHTMLExport()
    {
        // get process, if not already running or export is up-to-date, return corresponding status
        echo $this->getProcess();
        exit;
    }

    /**
     * Start user html export
     */
    public function startUserHTMLExport()
    {
        ignore_user_abort(true);
        // do the export
        include_once("./Modules/Wiki/classes/class.ilWikiHTMLExport.php");
        $exp = new ilWikiHTMLExport($this->wiki);
        $exp->setMode(ilWikiHTMLExport::MODE_USER);
        $exp->buildExportFile();
        // reset user export status
        $this->updateStatus(100, self::NOT_RUNNING);
        exit;
    }

    /**
     * Deliver file
     */
    public function deliverFile()
    {
        $this->log->debug("deliver");
        include_once("./Modules/Wiki/classes/class.ilWikiHTMLExport.php");
        $exp = new ilWikiHTMLExport($this->wiki);
        $exp->setMode(ilWikiHTMLExport::MODE_USER);
        $file = $exp->getUserExportFile();
        $this->log->debug("file: " . $file);
        ilUtil::deliverFile($file, pathinfo($file, PATHINFO_BASENAME));
    }
}
