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
 * Class ilChatroomSmilies
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmilies
{
    /**
     * Inserts default smiley set
     */
    private static function _insertDefaultValues(): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $values = [
            ["icon_smile.gif", ":)\n:-)\n:smile:"],
            ["icon_wink.gif", ";)\n;-)\n:wink:"],
            ["icon_laugh.gif", ":D\n:-D\n:laugh:\n:grin:\n:biggrin:"],
            ["icon_sad.gif", ":(\n:-(\n:sad:"],
            ["icon_shocked.gif", ":o\n:-o\n:shocked:"],
            ["icon_tongue.gif", ":p\n:-p\n:tongue:"],
            ["icon_cool.gif", ":cool:"],
            ["icon_eek.gif", ":eek:"],
            ["icon_angry.gif", ":||\n:-||\n:angry:"],
            ["icon_flush.gif", ":flush:"],
            ["icon_idea.gif", ":idea:"],
            ["icon_thumbup.gif", ":thumbup:"],
            ["icon_thumbdown.gif", ":thumbdown:"],
        ];

        $stmt = $ilDB->prepareManip(
            'INSERT INTO chatroom_smilies (smiley_id, smiley_keywords, smiley_path) VALUES (?, ?, ?)',
            ['integer', 'text', 'text']
        );

        foreach ($values as $val) {
            $row = [
                $ilDB->nextId('chatroom_smilies'),
                $val[1],
                $val[0]
            ];
            $stmt->execute($row);
        }
    }

    /**
     * Checks if smiley folder is available; if not
     * it will try to create folder and performs
     * actions for an initial smiley set
     * @return boolean
     */
    public static function _checkSetup(): bool
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        /** @var ilLanguage $lng */
        $lng = $DIC->language();

        $path = self::_getSmileyDir();

        if (!is_dir($path)) {
            $main_tpl->setOnScreenMessage('info', $lng->txt('chatroom_smilies_dir_not_exists'));
            ilFileUtils::makeDirParents($path);

            if (!is_dir($path)) {
                $main_tpl->setOnScreenMessage('failure', $lng->txt('chatroom_smilies_dir_not_available'));
                return false;
            }

            $smilies = [
                "icon_smile.gif",
                "icon_wink.gif",
                "icon_laugh.gif",
                "icon_sad.gif",
                "icon_shocked.gif",
                "icon_tongue.gif",
                "icon_cool.gif",
                "icon_eek.gif",
                "icon_angry.gif",
                "icon_flush.gif",
                "icon_idea.gif",
                "icon_thumbup.gif",
                "icon_thumbdown.gif",
            ];

            foreach ($smilies as $smiley) {
                copy("templates/default/images/emoticons/$smiley", $path . "/$smiley");
            }

            self::_insertDefaultValues();
            $main_tpl->setOnScreenMessage('success', $lng->txt('chatroom_smilies_initialized'));
        }

        if (!is_writable($path)) {
            $main_tpl->setOnScreenMessage('info', $lng->txt('chatroom_smilies_dir_not_writable'));
        }

        return true;
    }

    public static function _getSmileyDir(): string
    {
        return ilFileUtils::getWebspaceDir() . '/chatroom/smilies';
    }

    /**
     * @return array{smiley_id: int, smiley_keywords: string, smiley_path: string, smiley_fullpath: string}[]
     */
    public static function _getSmilies(): array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $res = $ilDB->query("SELECT smiley_id, smiley_keywords, smiley_path FROM chatroom_smilies");
        $result = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            $result[] = [
                'smiley_id' => (int) $row['smiley_id'],
                'smiley_keywords' => $row['smiley_keywords'],
                'smiley_path' => $row['smiley_path'],
                'smiley_fullpath' => ilFileUtils::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
            ];
        }

        return $result;
    }

    /**
     * @param int[] $ids
     */
    public static function _deleteMultipleSmilies(array $ids = []): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $smilies = self::_getSmiliesById($ids);

        if ($smilies === []) {
            return;
        }

        $sql_parts = [];

        foreach ($smilies as $s) {
            unlink($s['smiley_fullpath']);
            $sql_parts[] = 'smiley_id = ' . $ilDB->quote($s['smiley_id'], 'integer');
        }

        $ilDB->manipulate('DELETE FROM chatroom_smilies WHERE ' . implode(' OR ', $sql_parts));
    }

    /**
     * @param int[] $ids
     * @return array{smiley_id: int, smiley_keywords: string, smiley_path: string, smiley_fullpath: string}[]
     */
    public static function _getSmiliesById(array $ids = []): array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        if ($ids === []) {
            return [];
        }

        $sql = 'SELECT smiley_id, smiley_keywords, smiley_path FROM chatroom_smilies WHERE ';

        $sql_parts = [];
        foreach ($ids as $id) {
            $sql_parts[] = "smiley_id = " . $ilDB->quote($id, "integer");
        }

        $sql .= implode(" OR ", $sql_parts);
        $res = $ilDB->query($sql);
        $result = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            $result[] = [
                'smiley_id' => (int) $row['smiley_id'],
                'smiley_keywords' => $row['smiley_keywords'],
                'smiley_path' => $row['smiley_path'],
                'smiley_fullpath' => ilFileUtils::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
            ];
        }

        return $result;
    }

    /**
     * Updates smiley in DB by keyword and id from given array
     * ($data["smiley_keywords"], $data["smiley_id"])
     * @param array{smiley_id: int, smiley_keywords: string, smiley_path?: string, smiley_fullpath?: string} $data
     */
    public static function _updateSmiley(array $data): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            'UPDATE chatroom_smilies SET smiley_keywords = %s WHERE smiley_id = %s',
            ['text', 'integer'],
            [$data['smiley_keywords'], $data['smiley_id']]
        );

        if (isset($data["smiley_path"])) {
            $sm = self::_getSmiley($data["smiley_id"]);
            unlink($sm["smiley_fullpath"]);
            $ilDB->manipulateF(
                'UPDATE chatroom_smilies SET smiley_path = %s WHERE smiley_id = %s',
                ['text', 'integer'],
                [$data['smiley_path'], $data['smiley_id']]
            );
        }
    }

    /**
     * @param int $a_id
     * @return array{smiley_id: int, smiley_keywords: string, smiley_path: string, smiley_fullpath: string}
     */
    public static function _getSmiley(int $a_id): array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT smiley_id, smiley_keywords, smiley_path FROM chatroom_smilies WHERE smiley_id = %s ',
            ['integer'],
            [$a_id]
        );

        if ($row = $ilDB->fetchAssoc($res)) {
            return [
                'smiley_id' => (int) $row['smiley_id'],
                'smiley_keywords' => $row['smiley_keywords'],
                'smiley_path' => $row['smiley_path'],
                'smiley_fullpath' => ilFileUtils::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
            ];
        }

        throw new OutOfBoundsException("Smiley with id $a_id not found");
    }

    public static function getSmiliesBasePath(): string
    {
        return 'chatroom/smilies';
    }

    public static function _deleteSmiley(int $a_id): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        try {
            $smiley = self::_getSmiley($a_id);
            $path = ilFileUtils::getWebspaceDir() . '/chatroom/smilies/' . $smiley['smiley_path'];

            if (is_file($path)) {
                unlink($path);
            }

            $ilDB->manipulateF(
                'DELETE FROM chatroom_smilies WHERE smiley_id = %s',
                ['integer'],
                [$a_id]
            );
        } catch (Exception $e) {
        }
    }

    /**
     * Stores smiley with given keywords and path in database.
     * @param string $keywords
     * @param string $path
     */
    public static function _storeSmiley(string $keywords, string $path): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC->database();

        $stmt = $ilDB->prepareManip(
            'INSERT INTO chatroom_smilies (smiley_id, smiley_keywords, smiley_path) VALUES (?, ?, ?)',
            ['integer', 'text', 'text']
        );
        $ilDB->execute($stmt, [
            $ilDB->nextId('chatroom_smilies'),
            $keywords,
            $path
        ]);
    }

    /**
     * Trims given keywords and returns them in one array.
     * @param string $words
     * @return string[]
     */
    public static function _prepareKeywords(string $words): array
    {
        return array_filter(array_map('trim', explode("\n", $words)));
    }
}
