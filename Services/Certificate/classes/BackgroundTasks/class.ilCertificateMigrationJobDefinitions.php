<?php
/*
  +----------------------------------------------------------------------------+
  | ILIAS open source                                                          |
  +----------------------------------------------------------------------------+
  | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
  |                                                                            |
  | This program is free software; you can redistribute it and/or              |
  | modify it under the terms of the GNU General Public License                |
  | as published by the Free Software Foundation; either version 2             |
  | of the License, or (at your option) any later version.                     |
  |                                                                            |
  | This program is distributed in the hope that it will be useful,            |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
  | GNU General Public License for more details.                               |
  |                                                                            |
  | You should have received a copy of the GNU General Public License          |
  | along with this program; if not, write to the Free Software                |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
  +----------------------------------------------------------------------------+
*/

/**
 * Class ilCertificateMigrationJobDefinitions
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationJobDefinitions
{
    // table name for ilCertificateMigrationJob
    const CERT_MIGRATION_JOB_TABLE = 'bgtask_cert_migration';
    // task was not started yet
    const CERT_MIGRATION_STATE_INIT = 'not started';
    // task was started but is not running yet, maybe this will never be seen
    const CERT_MIGRATION_STATE_STARTED = 'started';
    // task is currently running
    const CERT_MIGRATION_STATE_RUNNING = 'running';
    // task was stopped manually or by timeout
    const CERT_MIGRATION_STATE_STOPPED = 'stopped';
    // task has finished
    const CERT_MIGRATION_STATE_FINISHED = 'finished';
    // task has stopped because something failed
    const CERT_MIGRATION_STATE_FAILED = 'failed';

}
/*
 * DBUPDATE
<?php
if(!$ilDB->tableExists('bgtask_cert_migration')) {
    $ilDB->createTable('bgtask_cert_migration', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'lock' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'found_items' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'processed_items' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'progress' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'state' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true
        ),
        'started_ts' => array(
            'type' => 'timestamp',
            'notnull' => false,
        ),
        'finished_ts' => array(
            'type' => 'timestamp',
            'notnull' => false,
        ),
    ));
    $ilDB->addPrimaryKey('bgtask_cert_migration', array('id'));
    $ilDB->createSequence('bgtask_cert_migration');
    $ilDB->addUniqueConstraint('bgtask_cert_migration', array('id', 'usr_id'));
}
$ilCtrlStructureReader->getStructure();
?>
 */