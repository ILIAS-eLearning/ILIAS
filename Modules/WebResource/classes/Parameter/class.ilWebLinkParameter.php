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
 * Immutable class for parameters attached to Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkParameter extends ilWebLinkBaseParameter
{
    private int $webr_id;
    private int $link_id;
    private int $param_id;

    private ilObjUser $user;

    public function __construct(
        ilObjUser $user,
        int $webr_id,
        int $link_id,
        int $param_id,
        int $value,
        string $name
    ) {
        $this->user = $user;
        $this->webr_id = $webr_id;
        $this->link_id = $link_id;
        $this->param_id = $param_id;
        parent::__construct($value, $name);
    }

    //TODO inject the dependency on DIC->user as an argument into this method
    public function appendToLink(string $link) : string
    {
        if (!strpos($link, '?')) {
            $link .= "?";
        } else {
            $link .= "&";
        }
        $link .= ($this->getName() . "=");
        switch ($this->getValue()) {
            case self::VALUES['login']:
                $link .= (urlencode(
                    $this->user->getLogin()
                ));
                break;

            case self::VALUES['session_id']:
                $link .= (session_id());
                break;

            case self::VALUES['user_id']:
                $link .= ($this->user->getId());
                break;

            case self::VALUES['matriculation']:
                $link .= ($this->user->getMatriculation());
                break;

            default:
                throw new ilWebLinkParameterException(
                    'Invalid parameter value'
                );
        }

        return $link;
    }

    public function getInfo() : string
    {
        $info = $this->getName();

        switch ($this->getValue()) {
            case self::VALUES['user_id']:
                return $info . '=USER_ID';

            case self::VALUES['session_id']:
                return $info . '=SESSION_ID';

            case self::VALUES['login']:
                return $info . '=LOGIN';

            case self::VALUES['matriculation']:
                return $info . '=MATRICULATION';

            default:
                throw new ilWebLinkParameterException(
                    'Invalid parameter value'
                );
        }
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        switch ($this->getValue()) {
            case self::VALUES['user_id']:
                $value = 'userId';
                break;

            case self::VALUES['login']:
                $value = 'userName';
                break;

            case self::VALUES['matriculation']:
                $value = 'matriculation';
                break;

            default:
                return;
        }

        $writer->xmlElement(
            'DynamicParameter',
            [
                'id' => $this->getParamId(),
                'name' => $this->getName(),
                'type' => $value
            ]
        );
    }

    public function getWebrId() : int
    {
        return $this->webr_id;
    }

    public function getLinkId() : int
    {
        return $this->link_id;
    }

    public function getParamId() : int
    {
        return $this->param_id;
    }
}
