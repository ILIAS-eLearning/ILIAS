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
 * Class ilPCLoginPageElement
 *
 * Login page element object (see ILIAS DTD). Inserts login page elements
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilPCLoginPageElement extends ilPageContent
{
    private static array $types = array(
        'login-form' => 'login_form',
        'cas-login-form' => 'cas_login_form',
        'shibboleth-login-form' => 'shib_login_form',
        'openid-connect-login' => 'openid_connect_login',
        'registration-link' => 'registration_link',
        'user-agreement' => 'user_agreement_link',
        'dpro-agreement' => 'dpro_agreement_link',
        'saml-login' => 'saml_login'
    );

    /**
     * Get all types
     */
    public static function getAllTypes(): array
    {
        return self::$types;
    }

    public function init(): void
    {
        $this->setType('lpe');
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "LoginPageElement");
    }

    public function setLoginPageElementType(string $a_type): void
    {
        if (!empty($a_type)) {
            $this->getChildNode()->setAttribute('Type', $a_type);
        }
    }

    public function getLoginPageElementType(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute('Type');
        }
        return "";
    }

    public function setAlignment(string $a_alignment): void
    {
        $this->getChildNode()->setAttribute('HorizontalAlign', $a_alignment);
    }

    public function getAlignment(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute('HorizontalAlign');
        }
        return "";
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_login_page_element");
    }
}
