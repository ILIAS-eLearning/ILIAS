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
 * Show glossary terms
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjLinkResourceSubItemListGUI extends ilSubItemListGUI
{
    public function getHTML() : string
    {
        $this->lng->loadLanguageModule('webr');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) && strlen(
                $this->getHighlighter()->getContent(
                    $this->getObjId(),
                    $sub_item
                )
            )) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable(
                    'TXT_FRAGMENT',
                    $this->getHighlighter()->getContent(
                        $this->getObjId(),
                        $sub_item
                    )
                );
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $this->lng->txt('webr'));
            $this->tpl->setVariable('SEPERATOR', ':');

            $link_data = ilLinkResourceItems::lookupItem(
                $this->getObjId(),
                $sub_item
            );
            $link_data = ilParameterAppender::_append($link_data);

            // handle internal links (#10620)
            if (stristr($link_data["target"], "|")) {
                $parts = explode("|", $link_data["target"]);
                if ($parts[0] == "page") {
                    $parts[0] = "pg";
                }
                if ($parts[0] == "term") {
                    $parts[0] = "git";
                }
                $link_data["target"] = ilLink::_getStaticLink(
                    $parts[1],
                    $parts[0]
                );
            }

            $this->tpl->setVariable('LINK', $link_data['target']);
            $this->tpl->setVariable('TARGET', '_blank');
            $this->tpl->setVariable('TITLE', $link_data['title']);

            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }
            $this->tpl->parseCurrentBlock();
        }

        $this->showDetailsLink();
        return $this->tpl->get();
    }
}
