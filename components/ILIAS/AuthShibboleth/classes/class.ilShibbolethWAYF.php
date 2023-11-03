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

use ILIAS\HTTP\Wrapper\WrapperFactory;

/**
 * Class ShibbolethWAYF
 *
 * This class handles the Home Organization selection (also called Where Are You
 * From service) process for Shibboleth users.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesAuthShibboleth
 */
class ilShibbolethWAYF
{
    public const COOKIE_NAME_SAML_IDP = '_saml_idp';
    public bool $is_selection = false;
    public bool $is_valid_selection = false;
    public string $selected_idp = '-';
    public array $idp_list = [];
    protected WrapperFactory $wrapper;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected \ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

        // Was the WAYF form submitted?
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->settings = $DIC->settings();
        $this->is_selection = $this->wrapper->post()->has('home_organization_selection');
        $this->lng = $DIC->isDependencyAvailable('language')
            ? $DIC->language()
            : new ilLanguage(
                $this->wrapper->query()->has('lang')
                ? $this->wrapper->query()->retrieve('lang', $DIC->refinery()->to()->string())
                : null
            );

        // Was selected IdP a valid
        $this->idp_list = $this->getIdplist();
        $idp_selection = $this->wrapper->post()->has('idp_selection')
            ? $this->wrapper->post()->retrieve('idp_selection', $DIC->refinery()->to()->string())
            : null;
        if ($idp_selection !== null
            && $idp_selection !== '-'
            && isset($this->idp_list[$idp_selection])
        ) {
            $this->is_valid_selection = true;
            $this->selected_idp = $idp_selection;
        } else {
            $this->is_valid_selection = false;
        }
    }

    public function isSelection(): bool
    {
        return $this->is_selection;
    }

    public function isValidSelection(): bool
    {
        return $this->is_valid_selection;
    }

    public function generateSelection(): string
    {
        $_saml_idp = $this->wrapper->cookie()->has(self::COOKIE_NAME_SAML_IDP)
            ? $this->wrapper->cookie()->retrieve(
                self::COOKIE_NAME_SAML_IDP,
                $this->refinery->kindlyTo()->string()
            )
            : null;
        $idp_cookie = $this->generateCookieArray($_saml_idp);

        $selectedIDP = null;
        if ($idp_cookie !== [] && isset($this->idp_list[end($idp_cookie)])) {
            $selectedIDP = end($idp_cookie);
            $selectElement = '
		<select name="idp_selection">
			<option value="-">' . $this->lng->txt("shib_member_of") . '</option>';
        } else {
            $selectElement = '
		<select name="idp_selection">
			<option value="-" selected="selected">' . $this->lng->txt("shib_member_of") . '</option>';
        }

        foreach ($this->idp_list as $idp_id => $idp_data) {
            if ($idp_id == $selectedIDP) {
                $selectElement .= '<option value="' . $idp_id . '" selected="selected">' . $idp_data[0] . '</option>';
            } else {
                $selectElement .= '<option value="' . $idp_id . '">' . $idp_data[0] . '</option>';
            }
        }

        return $selectElement . '
		</select>';
    }

    /**
     * @description Redirects user to the local Shibboleth session initatiotor with already set GET arguments for the right IdP and return location.
     */
    public function redirect(): void
    {
        // Where to return after the authentication process
        $target = $this->wrapper->post()->has('il_target')
            ? $this->wrapper->post()->retrieve('il_target', $this->refinery->kindlyTo()->string())
            : '';
        $target = trim(ILIAS_HTTP_PATH, '/') . '/shib_login.php?target=' . $target;
        $idp_data = $this->idp_list[$this->selected_idp];
        if (isset($idp_data[1])) {
            ilUtil::redirect($idp_data[1] . '?providerId=' . urlencode($this->selected_idp) . '&target='
                . urlencode($target));
        } else {
            // TODO: This has to be changed to /Shibboleth.sso/DS?entityId= for
            // Shibbolet 2.x sometime...
            ilUtil::redirect('/Shibboleth.sso?providerId=' . urlencode($this->selected_idp) . '&target='
                . urlencode($target));
        }
    }

    /**
     * @description Sets the standard SAML domain cookie that is also used to preselect the right entry on the local wayf
     */
    public function setSAMLCookie(): void
    {
        $_saml_idp = $this->wrapper->cookie()->retrieve(self::COOKIE_NAME_SAML_IDP, $this->refinery->kindlyTo()->string());
        $arr_idps = $_saml_idp ? $this->generateCookieArray($_saml_idp) : [];
        $arr_idps = $this->appendCookieValue($this->selected_idp, $arr_idps);
        setcookie(self::COOKIE_NAME_SAML_IDP, $this->generateCookieValue($arr_idps), time() + (100 * 24 * 3600), '/');
    }

    /**
     * @description Show notice in case no IdP was selected
     */
    public function showNotice(): string
    {
        if (!$this->isSelection() || $this->isValidSelection()) {
            return '';
        }

        return $this->lng->txt("shib_invalid_home_organization");
    }

    /**
     * @description Generate array of IdPs from ILIAS Shibboleth settings
     * @return array<string, string[]>
     */
    public function getIdplist(): array
    {
        $idp_list = [];
        $idp_raw_list = explode("\n", $this->settings->get("shib_idp_list"));
        foreach ($idp_raw_list as $idp_line) {
            $idp_data = explode(',', $idp_line);
            if (isset($idp_data[2])) {
                $idp_list[trim($idp_data[0])] = array(trim($idp_data[1]), trim($idp_data[2]));
            } elseif (isset($idp_data[1])) {
                $idp_list[trim($idp_data[0])] = array(trim($idp_data[1]));
            }
        }

        return $idp_list;
    }

    /**
     * @description Generates an array of IDPs using the cookie value
     * @return bool[]|string[]
     */
    public function generateCookieArray(?string $value): array
    {
        if (null === $value) {
            return [];
        }
        $arr_cookie = explode(' ', $value);
        return array_map('base64_decode', $arr_cookie);
    }

    /**
     * @description Generate the value that is stored in the cookie using the list of IDPs
     */
    public function generateCookieValue(array $arr_cookie): string
    {
        $arr_cookie = array_map('base64_encode', $arr_cookie);
        return implode(' ', $arr_cookie);
    }

    /**
     * @description Append a value to the array of IDPs
     * @return mixed[]
     */
    public function appendCookieValue(string $value, array $arr_cookie): array
    {
        $arr_cookie[] = $value;
        $arr_cookie = array_reverse($arr_cookie);
        $arr_cookie = array_unique($arr_cookie);
        return array_reverse($arr_cookie);
    }
}
