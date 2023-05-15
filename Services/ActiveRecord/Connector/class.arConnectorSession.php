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
 * Class arConnectorSession
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConnectorSession extends arConnector
{
    public const AR_CONNECTOR_SESSION = 'arConnectorSession';

    public static function resetSession(): void
    {
        $_SESSION[self::AR_CONNECTOR_SESSION] = [];
    }

    /**
     * @return mixed[]
     */
    public static function getSession(): array
    {
        if (!$_SESSION[self::AR_CONNECTOR_SESSION]) {
            self::resetSession();
        }

        return $_SESSION[self::AR_CONNECTOR_SESSION];
    }

    /**
     * @return mixed[]
     */
    public static function getSessionForActiveRecord(ActiveRecord $activeRecord): array
    {
        $session = self::getSession();
        $ar_session = $session[$activeRecord::returnDbTableName()];
        if (!is_array($ar_session)) {
            return [];
        }

        return $ar_session;
    }

    public function checkConnection(ActiveRecord $activeRecord): bool
    {
        return is_array(self::getSession());
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $activeRecord): int
    {
        return count(self::getSessionForActiveRecord($activeRecord)) + 1;
    }

    public function installDatabase(ActiveRecord $activeRecord, array $fields): bool
    {
        return $this->resetDatabase($activeRecord);
    }

    public function updateDatabase(ActiveRecord $activeRecord): bool
    {
        return true;
    }

    public function resetDatabase(ActiveRecord $activeRecord): bool
    {
        $_SESSION[self::AR_CONNECTOR_SESSION][$activeRecord::returnDbTableName()] = [];

        return true;
    }

    public function truncateDatabase(ActiveRecord $activeRecord): bool
    {
        return $this->resetDatabase($activeRecord);
    }

    public function checkTableExists(ActiveRecord $activeRecord): bool
    {
        return is_array(self::getSessionForActiveRecord($activeRecord));
    }

    public function checkFieldExists(ActiveRecord $activeRecord, string $field_name): bool
    {
        $session = self::getSessionForActiveRecord($activeRecord);

        return array_key_exists($field_name, $session[0]);
    }

    /**
     * @throws arException
     */
    public function removeField(ActiveRecord $activeRecord, string $field_name): bool
    {
        return true;
    }

    /**
     * @throws arException
     */
    public function renameField(ActiveRecord $activeRecord, string $old_name, string $new_name): bool
    {
        return true;
    }

    public function create(ActiveRecord $activeRecord): void
    {
        $_SESSION[self::AR_CONNECTOR_SESSION][$activeRecord::returnDbTableName()][$activeRecord->getPrimaryFieldValue(
        )] = $activeRecord->asStdClass();
    }

    /**
     * @return mixed[]
     */
    public function read(ActiveRecord $activeRecord): array
    {
        $session = self::getSessionForActiveRecord($activeRecord);

        return [$session[$activeRecord->getPrimaryFieldValue()]];
    }

    public function update(ActiveRecord $activeRecord): void
    {
        $this->create($activeRecord);
    }

    public function delete(ActiveRecord $activeRecord): void
    {
        unset(
            $_SESSION[self::AR_CONNECTOR_SESSION][$activeRecord::returnDbTableName(
            )][$activeRecord->getPrimaryFieldValue()]
        );
    }

    /**
     * @return mixed[]
     * @internal param $q
     */
    public function readSet(ActiveRecordList $activeRecordList): array
    {
        $session = self::getSessionForActiveRecord($activeRecordList->getAR());
        foreach ($session as $i => $s) {
            $session[$i] = (array) $s;
        }
        foreach ($activeRecordList->getArWhereCollection()->getWheres() as $arWhere) {
            $fieldname = $arWhere->getFieldname();
            $v = $arWhere->getValue();
            $operator = $arWhere->getOperator();

            foreach ($session as $i => $s) {
                $session[$i] = (array) $s;
                if ($operator !== '=') {
                    continue;
                }
                if ($s[$fieldname] === $v) {
                    continue;
                }
                unset($session[$i]);
            }
        }

        return $session;
    }

    public function affectedRows(ActiveRecordList $activeRecordList): int
    {
        return count($this->readSet($activeRecordList));
    }

    /**
     * @param $value
     */
    public function quote($value, string $type): string
    {
        return $value;
    }

    public function updateIndices(ActiveRecord $activeRecord): void
    {
        // TODO: Implement updateIndices() method.
    }
}
