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
 * Class ilDatabaseException
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseException extends ilException
{
    public const DB_GENERAL = 10000;


    public function __construct(string $a_message, int $a_code = self::DB_GENERAL)
    {
        $a_message = $this->tranlateException($a_code) . $a_message;
        parent::__construct($a_message, $a_code);
    }


    protected function tranlateException(int $code): string
    {
        $message = 'An undefined Database Exception occured';
        if ($code === static::DB_GENERAL) {
            $message = 'An undefined Database Exception occured';
        }

        return $message . '. ';
    }
}
