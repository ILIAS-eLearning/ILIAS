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
 * ilExternalDetector Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilEventDetector.php
 * @see class.ilTimerDetector.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
interface ilExternalDetector extends ilDetector
{
    // Event listener table persistence scheme.

    public function setDbId(int $a_id) : void;

    /**
     * @return mixed
     */
    public function getDbId();

    public function hasDbId() : bool;

    public function writeDetectorToDb() : void;

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
