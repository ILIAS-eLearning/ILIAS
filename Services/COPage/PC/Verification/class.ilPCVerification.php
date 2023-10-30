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
 * Class ilPCVerification
 * Verification content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCVerification extends ilPageContent
{
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType('vrfc');
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Verification");
    }

    public function setData(
        string $a_type,
        int $a_id
    ): void {
        $this->getChildNode()->setAttribute('Type', $a_type);
        $this->getChildNode()->setAttribute('Id', (string) $a_id);
        $this->getChildNode()->setAttribute('User', (string) $this->user->getId());
    }

    /**
     * @return null|array<string, string>
     */
    public function getData(): ?array
    {
        if (is_object($this->getChildNode())) {
            return [
                'id' => $this->getChildNode()->getAttribute('Id'),
                'type' => $this->getChildNode()->getattribute('Type'),
                'user' => $this->getChildNode()->getAttribute('User')
            ];
        }

        return null;
    }

    /**
     * @return string[]
     */
    public static function getLangVars(): array
    {
        return [
            'pc_vrfc',
            'ed_insert_verification'
        ];
    }

    public static function isInPortfolioPage(
        ilPortfolioPage $a_page,
        string $a_type,
        int $a_id
    ): bool {
        // try to find verification in portfolio page
        $a_page->buildDom();

        $dom = $a_page->getDomDoc();

        $xpath_temp = new DOMXPath($dom);
        $nodes = $xpath_temp->query('//PageContent/Verification');
        foreach ($nodes as $node) {
            if ($node->getAttribute('Type') === $a_type && (int) $node->getAttribute('Id') === $a_id) {
                return true;
            }
        }

        return false;
    }
}
