<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Code;

use ILIAS\Survey\InternalDataService;

/**
 * DB survey codes (table
 * @author killing@leifos.de
 */
class CodeDBRepo
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;


    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    /**
     * Delete all codes of a survey
     */
    public function deleteAll(int $survey_id) : void
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM svy_anonymous WHERE " .
            " survey_fi = %s",
            ["integer"],
            [$survey_id]
        );
    }

    /**
     * Delete single code
     */
    public function delete(int $survey_id, string $code) : void
    {
        $db = $this->db;

        if ($code !== "") {
            $db->manipulateF(
                "DELETE FROM svy_anonymous WHERE " .
                " survey_fi = %s AND survey_key = %s",
                ["integer", "text"],
                [$survey_id, $code]
            );
        }
    }

    /**
     * Get a new unique code
     */
    protected function getNew(int $survey_id) : string
    {
        // create a 5 character code
        $codestring = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        mt_srand();
        $code = "";
        for ($i = 1; $i <= 5; $i++) {
            $index = random_int(0, strlen($codestring) - 1);
            $code .= substr($codestring, $index, 1);
        }
        // uniqueness
        while ($this->exists($survey_id, $code)) {
            $code = $this->getNew($survey_id);
        }
        return $code;
    }

    /**
     * Does code exist in survey?
     */
    public function exists(
        int $survey_id,
        string $code
    ) : bool {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT anonymous_id FROM svy_anonymous " .
            " WHERE survey_fi = %s AND survey_key = %s ",
            ["integer", "text"],
            [$survey_id, $code]
        );
        return ($set->numRows() > 0);
    }

    /**
     * Get user key for id
     */
    protected function getUserKey(int $user_id) : ?string
    {
        $user_key = ($user_id > 0)
            ? md5((string) $user_id)
            : null;
        return $user_key;
    }

    /**
     * Saves a survey access code for a registered user to the database
     * @return int new id
     * @throws \ilSurveyException
     */
    public function add(
        int $survey_id,
        string $code = "",
        int $user_id = 0,
        string $email = "",
        string $last_name = "",
        string $first_name = "",
        int $sent = 0,
        int $tstamp = 0
    ) : int {
        $db = $this->db;

        if ($code === "") {
            $code = $this->getNew($survey_id);
        }
        if ($this->exists($survey_id, $code)) {
            throw new \ilSurveyException("Code $code already exists.");
        }

        $user_key = $this->getUserKey($user_id);

        if ($tstamp === 0) {
            $tstamp = time();
        }

        $next_id = $db->nextId('svy_anonymous');

        $db->insert("svy_anonymous", [
            "anonymous_id" => ["integer", $next_id],
            "survey_key" => ["text", $code],
            "survey_fi" => ["integer", $survey_id],
            "user_key" => ["text", $user_key],
            "tstamp" => ["integer", $tstamp],
            "sent" => ["integer", $sent]
        ]);

        if ($email !== "" || $last_name !== "" || $first_name !== "") {
            $this->updateExternalData(
                $next_id,
                $email,
                $last_name,
                $first_name,
                $sent
            );
        }
        return $next_id;
    }

    /**
     * Add multiple codes
     * @param int $nr number of codes that should be generated/added
     * @return int[]
     * @throws \ilSurveyException
     */
    public function addCodes(
        int $survey_id,
        int $nr
    ) : array {
        $ids = [];
        while ($nr-- > 0) {
            $ids[] = $this->add($survey_id);
        }
        return $ids;
    }

    /**
     * Update external data of a code
     */
    public function updateExternalData(
        int $code_id,
        string $email,
        string $last_name,
        string $first_name,
        int $sent
    ) : bool {
        $ilDB = $this->db;

        $email = trim($email);

        if ($email === "" || ($email && !\ilUtil::is_email($email))) {
            return false;
        }

        $data = array("email" => $email,
                      "lastname" => trim($last_name),
                      "firstname" => trim($first_name));

        $fields = array(
            "externaldata" => array("text", serialize($data)),
            "sent" => array("integer", $sent)
        );

        $ilDB->update(
            "svy_anonymous",
            $fields,
            array("anonymous_id" => array("integer", $code_id))
        );

        return true;
    }

    /**
     * Get all access keys of a survey
     * @return string[]
     */
    public function getAll(
        int $survey_id
    ) : array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_key FROM svy_anonymous " .
            " WHERE survey_fi = %s ",
            ["integer"],
            [$survey_id]
        );
        $codes = [];
        while ($rec = $db->fetchAssoc($set)) {
            $codes[] = $rec["survey_key"];
        }
        return $codes;
    }

    /**
     * Get all codes of a survey
     * @return Code[]
     */
    public function getAllData(
        int $survey_id
    ) : array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM svy_anonymous " .
            " WHERE survey_fi = %s ",
            ["integer"],
            [$survey_id]
        );
        $codes = [];
        while ($rec = $db->fetchAssoc($set)) {
            $codes[] = $this->data->code($rec["survey_key"])
                ->withId((int) $rec["anonymous_id"])
                ->withSurveyId((int) $rec["survey_fi"])
                ->withUserKey((string) $rec["user_key"])
                ->withTimestamp((int) $rec["tstamp"])
                ->withSent((int) $rec["sent"])
                ->withEmail((string) $rec["email"])
                ->withFirstName((string) $rec["firstname"])
                ->withLastName((string) $rec["lastname"]);
        }

        return $codes;
    }

    public function getByUserKey(
        int $survey_id,
        string $survey_key
    ) : ?Code {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM svy_anonymous " .
            " WHERE survey_fi = %s AND survey_key = %s",
            ["integer", "string"],
            [$survey_id, $survey_key]
        );

        if ($rec = $db->fetchAssoc($set)) {
            $ext_data = unserialize((string) $rec["externaldata"], ["allowed_classes" => false]);
            return $this->data->code($rec["survey_key"])
                                  ->withId((int) $rec["anonymous_id"])
                                  ->withSurveyId((int) $rec["survey_fi"])
                                  ->withUserKey((string) $rec["user_key"])
                                  ->withTimestamp((int) $rec["tstamp"])
                                  ->withSent((int) $rec["sent"])
                                  ->withEmail((string) ($ext_data["email"] ?? ""))
                                  ->withFirstName((string) ($ext_data["firstname"] ?? ""))
                                  ->withLastName((string) ($ext_data["lastname"] ?? ""));
        }

        return null;
    }

    /**
     * Bind registered user to a code
     */
    public function bindUser(
        int $survey_id,
        string $code,
        int $user_id
    ) : void {
        $db = $this->db;

        $user_key = $this->getUserKey($user_id);

        $db->update(
            "svy_anonymous",
            [
            "user_key" => ["text", $user_key]
        ],
            [    // where
                "survey_id" => ["integer", $survey_id],
                "survey_key" => ["integer", $code]
            ]
        );
    }

    /**
     * Get code for a registered user
     */
    public function getByUserId(
        int $survey_id,
        int $user_id
    ) : string {
        $db = $this->db;

        $user_key = $this->getUserKey($user_id);

        $set = $db->queryF(
            "SELECT survey_key FROM svy_anonymous " .
            " WHERE survey_fi = %s AND user_key = %s ",
            ["integer", "string"],
            [$survey_id, $user_key]
        );
        $rec = $db->fetchAssoc($set);
        return $rec["survey_key"] ?? "";
    }
}
