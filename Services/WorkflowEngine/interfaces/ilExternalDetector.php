<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilExternalDetector Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilEventDetector.php
 * @see class.ilTimerDetector.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilExternalDetector extends ilDetector
{
    // Event listener table persistence scheme.

    /**
     * @param $a_id
     *
     * @return mixed
     */
    public function setDbId($a_id) : void;

    /**
     * @return mixed
     */
    public function getDbId();

    /**
     * @return mixed
     */
    public function hasDbId() : bool;

    /**
     * @return mixed
     */
    public function writeDetectorToDb() : void;

    /**
     * @return mixed
     */
    public function deleteDetectorFromDb() : void;

    // Listening only at certain times scheme.

    /**
     * @return mixed
     */
    public function isListening();

    /**
     * @return mixed
     */
    public function getListeningTimeframe();

    /**
     * @param integer $listening_start
     * @param integer $listening_end
     * @return mixed
     */
    public function setListeningTimeframe(int $listening_start, int $listening_end);

    // Event description scheme.

    /**
     * @return mixed
     */
    public function getEvent();

    /**
     * @return mixed
     */
    public function getEventSubject();

    /**
     * @return mixed
     */
    public function getEventContext();
}
