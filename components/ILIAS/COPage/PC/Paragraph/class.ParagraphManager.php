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

declare(strict_types=1);

namespace ILIAS\COPage\PC\Paragraph;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphManager
{
    protected \ILIAS\Repository\HTTP\HTTPUtil $http_util;
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->http_util = $DIC->copage()->internal()->gui()->httpUtil();
    }

    public function send(
        \DOMDocument $dom,
        string $par_id,
        string $filename
    ): void {
        $content = "";
        $path = "/descendant::Paragraph[position() = $par_id]";
        $nodes = $this->dom_util->path(
            $dom,
            $path
        );
        if (count($nodes) != 1) {
            throw new \ilCOPageException("Paragraph not found.");
        }

        $context_node = $nodes->item(0);
        foreach ($context_node->childNodes as $child) {
            $content .= $this->dom_util->dump($child);
        }

        $content = str_replace("<br />", "\n", $content);
        $content = str_replace("<br/>", "\n", $content);

        $plain_content = html_entity_decode($content);

        $this->http_util->deliverString($plain_content, $filename);
    }

    public function getFirstParagraphText(\ilPageObject $page): string
    {
        $dom = $page->getDomDoc();
        if ($dom) {
            $path = "//Paragraph[1]";
            $nodes = $this->dom_util->path($dom, $path);
            if (count($nodes) > 0) {
                $cont_node = $nodes->item(0)->parentNode;
                $par = new \ilPCParagraph($page);
                $par->setDomNode($cont_node);
                $text = $par->getText();
                return $text;
            }
        }
        return "";
    }

    public function getParagraphForPCID(\ilPageObject $page, string $pcid): ?\ilPCParagraph
    {
        $dom = $page->getDomDoc();
        if ($dom) {
            $path = "//PageContent[@PCID='" . $pcid . "']/Paragraph[1]";
            $nodes = $this->dom_util->path($dom, $path);
            if (count($nodes) > 0) {
                $cont_node = $nodes->item(0)->parentNode;
                $par = new \ilPCParagraph($page);
                $par->setDomNode($cont_node);
                return $par;
            }
        }
        return null;
    }
}
