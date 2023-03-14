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

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\File\Icon\IconDatabaseRepository;
use ILIAS\Modules\File\Settings\General;

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
     * adds a new table column called 'downloads' which is used to keep
     * track of the actual amount of downloads of a file object.
     * ---
     * NOTE: the initial value will be the collective sum of read_count
     * from the database table read_event of the tracking service. This
     * will not be an accurate representation of the download count, but
     * provides at least some insight.
     */
    public function step_2(): void
    {
        $this->abortIfNotPrepared();

        if (!$this->database->tableExists('file_data') ||
            $this->database->tableColumnExists('file_data', 'downloads')
        ) {
            return;
        }

        $this->database->addTableColumn(
            'file_data',
            'downloads',
            [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => 0 // will be adjusted in an update query.
            ]
        );

        $this->database->manipulate("
            UPDATE file_data SET downloads = (
                SELECT COALESCE(SUM(read_event.read_count), 0) FROM read_event 
                    WHERE read_event.obj_id = file_data.file_id
            );
        ");
    }

    /**
     * sets the default visibility of the amount of downloads to visible ('1' or true).
     */
    public function step_3(): void
    {
        $this->abortIfNotPrepared();

        /** copied from @see ilSetting::set() */
        $this->database->insert(
            'settings',
            [
                'module' => ['text', General::MODULE_NAME],
                'keyword' => ['text', General::F_SHOW_AMOUNT_OF_DOWNLOADS],
                'value' => ['text', '1'],
            ]
        );
    }

    /**
     * adds two new tables to store data concerning suffix-specific icons for files
     */
    public function step_4(): void
    {
        $this->abortIfNotPrepared();
        if (!$this->database->tableExists(IconDatabaseRepository::ICON_TABLE_NAME)) {
            $this->database->createTable(
                IconDatabaseRepository::ICON_TABLE_NAME,
                [
                    IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION => [
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true,
                        'default' => '',
                    ],
                    IconDatabaseRepository::ICON_ACTIVE => [
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false,
                        'default' => 0,
                    ],
                    IconDatabaseRepository::IS_DEFAULT_ICON => [
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false,
                        'default' => 0,
                    ]
                ]
            );
        }
        if (!$this->database->tableExists(IconDatabaseRepository::SUFFIX_TABLE_NAME)) {
            $this->database->createTable(
                IconDatabaseRepository::SUFFIX_TABLE_NAME,
                [
                    IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION => [
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true,
                        'default' => '',
                    ],
                    IconDatabaseRepository::SUFFIX => [
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false,
                        'default' => '',
                    ]
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
