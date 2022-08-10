<?php

declare(strict_types=1);

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
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilFileObjectDatabaseObjective implements ilDatabaseUpdateSteps
{
    private ?ilDBInterface $database = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    /**
     * adds a new table column called 'direct_download' that is used to
     * determine if the on-click action in the ilObjFileListGUI should
     * download the file directly or redirect to the objects info-page.
     * ---
     * NOTE: this won't affect the default-behaviour which currently
     * downloads the file directly, since '1' or true is added as the
     * default value to the new column.
     */
    public function step_1(): void
    {
        $this->abortIfNotPrepared();
        if ($this->database->tableExists('file_data')) {
            $this->database->addTableColumn(
                'file_data',
                'on_click_mode',
                [
                    'type' => 'integer',
                    'length' => '1',
                    'notnull' => '1',
                    'default' => ilObjFile::CLICK_MODE_DOWNLOAD,
                ]
            );
        }
    }

    /**
     * Halts the execution of these update steps if no database was
     * provided.
     * @throws LogicException if the database update steps were not
     *                        yet prepared.
     */
    private function abortIfNotPrepared(): void
    {
        if (null === $this->database) {
            throw new LogicException(self::class . "::prepare() must be called before db-update-steps execution.");
        }
    }
}
