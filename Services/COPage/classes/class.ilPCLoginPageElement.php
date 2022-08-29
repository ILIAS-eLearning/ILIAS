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
    public php4DOMElement $res_node;

    private static array $types = array(
        'login-form' => 'login_form',
        'cas-login-form' => 'cas_login_form',
        'shibboleth-login-form' => 'shib_login_form',
        'openid-connect-login' => 'openid_connect_login',
        'registration-link' => 'registration_link',
        'language-selection' => 'language_selection',
        'user-agreement' => 'user_agreement_link',
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

    /**
    * Set node
    */
    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);						// this is the PageContent node
        $this->res_node = $a_node->first_child();		// this is the login page element
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $lpe = $this->dom->create_element('LoginPageElement');
        $this->res_node = $this->node->append_child($lpe);
    }

    public function setLoginPageElementType(string $a_type): void
    {
        if (!empty($a_type)) {
            $this->res_node->set_attribute('Type', $a_type);
        }
    }

    public function getLoginPageElementType(): string
    {
        if (is_object($this->res_node)) {
            return $this->res_node->get_attribute('Type');
        }
        return "";
    }

    public function setAlignment(string $a_alignment): void
    {
        $this->res_node->set_attribute('HorizontalAlign', $a_alignment);
    }

    public function getAlignment(): string
    {
        if (is_object($this->res_node)) {
            return $this->res_node->get_attribute('HorizontalAlign');
        }
        return "";
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_login_page_element");
    }
}
