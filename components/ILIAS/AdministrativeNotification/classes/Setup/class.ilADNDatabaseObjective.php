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
 */

declare(strict_types=1);

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class ilADNDatabaseObjective implements ilDatabaseUpdateSteps
{
    private ?ilDBInterface $database = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }


    /**
     * Adds a new table column called 'has_language_limitation' which is used to define whether a notification is
     * only shown for certain languages or if it is shown for all languages.
     * Also adds a new table column called 'limited_to_languages' which specifies which languages a notification with
     * the above limitation is shown for.
     * ---
     * NOTE: the initial values will be set to 0 and "" respectively so that existing notifications will not be affected.
     */
    public function step_1(): void
    {
        $this->abortIfNotPrepared();

        if (!$this->database->tableExists('il_adn_notifications') ||
            $this->database->tableColumnExists('il_adn_notifications', 'has_language_limitation')
        ) {
            return;
        }
        $this->database->addTableColumn(
            'il_adn_notifications',
            'has_language_limitation',
            [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0,
            ]
        );
        $this->database->manipulate('
            UPDATE il_adn_notifications SET has_language_limitation = 0;
        ');

        if ($this->database->tableColumnExists('il_adn_notifications', 'limited_to_languages')
        ) {
            return;
        }
        $this->database->addTableColumn(
            'il_adn_notifications',
            'limited_to_languages',
            [
                'type' => 'text',
                'length' => 256,
                'notnull' => true,
                'default' => '',
            ]
        );
        $this->database->manipulate('
            UPDATE il_adn_notifications SET limited_to_languages = "[]";
        ');
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
            throw new LogicException(self::class . '::prepare() must be called before db-update-steps execution.');
        }
    }
}
