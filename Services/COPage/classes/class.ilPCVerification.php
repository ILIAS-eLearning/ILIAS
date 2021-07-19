<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilPCVerification
 * Verification content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCVerification extends ilPageContent
{
    private php4DOMElement $vrfc_node;
    protected ilObjUser $user;

    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("vrfc");
    }

    /**
     * @param php4DOMElement $a_node
     */
    public function setNode($a_node) : void
    {
        parent::setNode($a_node);        // this is the PageContent node
        $this->vrfc_node = $a_node->first_child();        // this is the verification node
    }

    public function create(ilPageObject $a_pg_obj, string $a_hier_id, string $a_pc_id = "") : void
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->vrfc_node = $this->dom->create_element("Verification");
        $this->vrfc_node = $this->node->append_child($this->vrfc_node);
    }

    public function setData(string $a_type, int $a_id) : void
    {
        $ilUser = $this->user;

        $this->vrfc_node->set_attribute("Type", $a_type);
        $this->vrfc_node->set_attribute("Id", $a_id);
        $this->vrfc_node->set_attribute("User", $ilUser->getId());
    }

    /**
     * @return null|array<string, string>
     */
    public function getData() : ?array
    {
        if (is_object($this->vrfc_node)) {
            return array("id" => $this->vrfc_node->get_attribute("Id"),
                         "type" => $this->vrfc_node->get_attribute("Type"),
                         "user" => $this->vrfc_node->get_attribute("User")
            );
        }

        return null;
    }

    /**
     * @return string[]
     */
    public static function getLangVars() : array
    {
        return array("pc_vrfc",
                     "ed_insert_verification"
        );
    }

    public static function isInPortfolioPage(ilPortfolioPage $a_page, string $a_type, int $a_id) : bool
    {
        // try to find verification in portfolio page
        $a_page->buildDom();
        $dom = $a_page->getDom();
        if ($dom instanceof php4DOMDocument) {
            $dom = $dom->myDOMDocument;
        }
        $xpath_temp = new DOMXPath($dom);
        $nodes = $xpath_temp->query("//PageContent/Verification");
        foreach ($nodes as $node) {
            if ($node->getAttribute("Type") == $a_type &&
                $node->getAttribute("Id") == $a_id) {
                return true;
            }
        }

        return false;
    }
}
