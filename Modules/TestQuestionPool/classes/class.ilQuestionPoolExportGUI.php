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
 * Export User Interface Class
 * @author       Michael Jansen <mjansen@databay.de>
 * @version      $Id$
 * @ingroup      ModulesTest
 */
class ilQuestionPoolExportGUI extends ilExportGUI
{
    /**
     * {@inheritdoc}
     */
    protected function buildExportTableGUI(): ilExportTableGUI
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionPoolExportTableGUI.php';
        $table = new ilQuestionPoolExportTableGUI($this, 'listExportFiles', $this->obj);
        return $table;
    }

    /**
     * Download file
     */
    public function download(): void
    {
        // @MBECKER Check if this is still needed.
        /*
        if (isset($_GET['file']) && $_GET['file']) {
            $_POST['file'] = array($_GET['file']);
        }
        // If not, remove the whole overwrite.
        */
        parent::download();
    }
}
