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
 * Class ilBadgeBackpack
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeBackpack
{
    public const URL_DISPLAYER = "https://backpack.openbadges.org/displayer/";

    protected string $email;
    protected int $uid;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(string $a_email)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->email = $a_email;
    }

    protected function authenticate(): bool
    {
        $json = $this->sendRequest(
            self::URL_DISPLAYER . "convert/email",
            array("email" => $this->email),
            true
        );

        if (!isset($json->status) ||
            $json->status !== "okay") {
            return false;
        }

        $this->uid = $json->userId;
        return true;
    }

    public function getGroups(): array
    {
        if ($this->authenticate()) {
            $json = $this->sendRequest(
                self::URL_DISPLAYER . $this->uid . "/groups.json"
            );

            $result = array();

            foreach ($json->groups as $group) {
                $result[$group->groupId] = array(
                    "title" => $group->name,
                    "size" => $group->badges
                );
            }

            return $result;
        }
        return [];
    }

    public function getBadges(string $a_group_id): ?array
    {
        if ($this->authenticate()) {
            $json = $this->sendRequest(
                self::URL_DISPLAYER . $this->uid . "/group/" . $a_group_id . ".json"
            );

            if ($json === null) {
                return null;
            }

            if (property_exists($json, 'status') && $json->status === "missing") {
                return null;
            }

            $result = [];

            foreach ($json->badges as $raw) {
                $badge = $raw->assertion->badge;

                // :TODO: not sure if this works reliably
                $issued_on = is_numeric($raw->assertion->issued_on)
                    ? $raw->assertion->issued_on
                    : strtotime($raw->assertion->issued_on);

                $result[] = [
                    "title" => $badge->name,
                    "description" => $badge->description,
                    "image_url" => $badge->image,
                    "criteria_url" => $badge->criteria,
                    "issuer_name" => $badge->issuer->name,
                    "issuer_url" => $badge->issuer->origin,
                    "issued_on" => new ilDate($issued_on, IL_CAL_UNIX)
                ];
            }

            return $result;
        }
        return null;
    }

    protected function sendRequest(
        string $a_url,
        array $a_param = array(),
        bool $a_is_post = false
    ): ?stdClass {
        try {
            $curl = new ilCurlConnection();
            $curl->init(false);

            $curl->setOpt(CURLOPT_FRESH_CONNECT, true);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_FORBID_REUSE, true);
            $curl->setOpt(CURLOPT_HEADER, 0);
            $curl->setOpt(CURLOPT_CONNECTTIMEOUT, 3);
            $curl->setOpt(CURLOPT_POSTREDIR, 3);

            // :TODO: SSL problems on test server
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

            $curl->setOpt(CURLOPT_HTTPHEADER, array(
                    "Accept: application/json",
                    "Expect:"
            ));

            if ($a_is_post) {
                $curl->setOpt(CURLOPT_POST, 1);
                if (count($a_param)) {
                    $curl->setOpt(CURLOPT_POSTFIELDS, http_build_query($a_param));
                }
            } else {
                $curl->setOpt(CURLOPT_HTTPGET, 1);
                if (count($a_param)) {
                    $a_url .= (strpos($a_url, "?") === false ? "?" : "") . http_build_query($a_param);
                }
            }
            $curl->setOpt(CURLOPT_URL, $a_url);

            $answer = $curl->exec();
        } catch (Exception $ex) {
            $this->main_tpl->setOnScreenMessage('failure', $ex->getMessage());
            return null;
        }

        return json_decode($answer, false, 512, JSON_THROW_ON_ERROR);
    }
}
