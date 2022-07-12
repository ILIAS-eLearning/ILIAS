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
 * Class ilParameterAppender
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ModulesWebResource
 */
class ilParameterAppender
{
    public const LINKS_TYPE_UNDEFINED = 0;
    public const LINKS_USER_ID = 1;
    public const LINKS_SESSION_ID = 2;
    public const LINKS_LOGIN = 3;
    public const LINKS_MATRICULATION = 4;
    public const LINKS_ERR_NONE = 0;
    public const LINKS_ERR_NO_NAME = 1;
    public const LINKS_ERR_NO_VALUE = 2;
    public const LINKS_ERR_NO_NAME_VALUE = 3;

    private int $webr_id;
    private int $err = self::LINKS_ERR_NONE;
    private string $name = '';
    private int $value = self::LINKS_TYPE_UNDEFINED;

    protected ilDBInterface $db;
    protected ilSetting $settings;

    public function __construct(int $webr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->webr_id = $webr_id;
    }

    /**
     * Get parameter ids of link
     * @return int[]
     */
    public static function getParameterIds(
        int $a_webr_id,
        int $a_link_id
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM webr_params " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_link_id, 'integer');
        $res = $ilDB->query($query);
        $params = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $params[] = (int) $row['param_id'];
        }
        return $params;
    }

    public function getErrorCode() : int
    {
        return $this->err;
    }

    public function setObjId(int $a_obj_id) : void
    {
        $this->webr_id = $a_obj_id;
    }

    public function getObjId() : int
    {
        return $this->webr_id;
    }

    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setValue(int $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : int
    {
        return $this->value;
    }

    public function validate() : bool
    {
        $this->err = self::LINKS_ERR_NONE;

        if (!strlen($this->getName()) && !$this->getValue()) {
            $this->err = self::LINKS_ERR_NO_NAME_VALUE;
            return false;
        }
        if (!strlen($this->getName())) {
            $this->err = self::LINKS_ERR_NO_NAME;
            return false;
        }
        if (!$this->getValue()) {
            $this->err = self::LINKS_ERR_NO_VALUE;
            return false;
        }
        return true;
    }

    public function add(int $a_link_id) : int
    {
        if (!$a_link_id) {
            return 0;
        }
        if (!strlen($this->getName()) || $this->getValue()) {
            return 0;
        }

        $next_id = $this->db->nextId('webr_params');
        $query = "INSERT INTO webr_params (param_id,webr_id,link_id,name,value) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjId(), 'integer') . ", " .
            $this->db->quote($a_link_id, 'integer') . ", " .
            $this->db->quote($this->getName(), 'text') . ", " .
            $this->db->quote($this->getValue(), 'integer') .
            ")";
        $res = $this->db->manipulate($query);

        return $next_id;
    }

    public function delete($a_param_id) : bool
    {
        $query = "DELETE FROM webr_params " .
            "WHERE param_id = " . $this->db->quote(
                $a_param_id,
                'integer'
            ) . " " .
            "AND webr_id = " . $this->db->quote($this->getObjId(), 'integer');
        $res = $this->db->manipulate($query);
        return true;
    }

    /**
     * Check if dynamic parameters are enabled
     */
    public static function _isEnabled() : bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        return (bool) $ilSetting->get('links_dynamic', '');
    }

    public static function _append(array $a_link_data) : array
    {
        global $DIC;

        $ilUser = $DIC->user();
        if (count(
            $params = ilParameterAppender::_getParams($a_link_data['link_id'])
        )) {
            // Check for prefix
            foreach ($params as $param_data) {
                if (!strpos($a_link_data['target'], '?')) {
                    $a_link_data['target'] .= "?";
                } else {
                    $a_link_data['target'] .= "&";
                }
                $a_link_data['target'] .= ($param_data['name'] . "=");
                switch ($param_data['value']) {
                    case self::LINKS_LOGIN:
                        $a_link_data['target'] .= (urlencode(
                            $ilUser->getLogin()
                        ));
                        break;

                    case self::LINKS_SESSION_ID:
                        $a_link_data['target'] .= (session_id());
                        break;

                    case self::LINKS_USER_ID:
                        $a_link_data['target'] .= ($ilUser->getId());
                        break;

                    case self::LINKS_MATRICULATION:
                        $a_link_data['target'] .= ($ilUser->getMatriculation());
                        break;
                }
            }
        }
        return $a_link_data;
    }

    /**
     * Get dynamic parameter definitions
     */
    public static function _getParams(int $a_link_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $params = [];

        $res = $ilDB->query(
            "SELECT * FROM webr_params WHERE link_id = " .
            $ilDB->quote($a_link_id, 'integer')
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $params[$row->param_id]['name'] = (string) $row->name;
            $params[$row->param_id]['value'] = (int) $row->value;
        }
        return $params;
    }

    /**
     * Get info text describing an existing dynamic link
     */
    public static function parameterToInfo(
        string $a_name,
        int $a_value
    ) : string {
        $info = $a_name;

        switch ($a_value) {
            case self::LINKS_USER_ID:
                return $info . '=USER_ID';

            case self::LINKS_SESSION_ID:
                return $info . '=SESSION_ID';

            case self::LINKS_LOGIN:
                return $info . '=LOGIN';

            case self::LINKS_MATRICULATION:
                return $info . '=MATRICULATION';
        }
        return '';
    }

    public static function _deleteAll(int $a_webr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM webr_params WHERE webr_id = " .
            $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Get options as array
     */
    public static function _getOptionSelect() : array
    {
        global $DIC;

        $lng = $DIC->language();
        return [
            0 => $lng->txt('links_select_one'),
            self::LINKS_USER_ID => $lng->txt('links_user_id'),
            self::LINKS_LOGIN => $lng->txt('links_user_name'),
            self::LINKS_SESSION_ID => $lng->txt('links_session_id'),
            self::LINKS_MATRICULATION => $lng->txt('matriculation')
        ];
    }
}
