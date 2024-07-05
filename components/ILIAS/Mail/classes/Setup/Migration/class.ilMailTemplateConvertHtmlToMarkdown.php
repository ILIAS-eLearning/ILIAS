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

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

/**
 * ilMailTemplateConvertHtmlToMarkdown
 *
 * Replaces all known HTML tags with their markdown equivalent
 * and removes other HTML for fields that have been converted
 * to markdown fields
 */
class ilMailTemplateConvertHtmlToMarkdown implements Migration
{
    public const NUMBER_OF_STEPS = 10000;
    public const TRANLSATION_TABLE = "mail_man_tpl";
    protected $tags = [
        '</b>', '</strong>', '</i>', '</em>', '</u>',
        '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>',
        '</ul>', '</ol>', '</a>', '<img',
        '<br>', '<br/>', '<br />', '</p>'
    ];

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "ilMailTemplateConvertHtmlToMarkdown";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::NUMBER_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $data = $this->getNextObject();
        if (count($data) > 0) {
            $m_message = $this->convert($data["m_message"]);

            $q = "UPDATE " . self::TRANLSATION_TABLE . PHP_EOL
                . " SET m_message = " . $this->db->quote($m_message, "string") . PHP_EOL
                . " WHERE tpl_id = " . $this->db->quote($data["tpl_id"], "integer") . PHP_EOL
                . " AND lang = " . $this->db->quote($data["lang"], "string");
            $res = $this->db->query($q);
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!$this->db->tableExists(self::TRANLSATION_TABLE)) {
            return 0;
        }
        $regex = "'" . implode('|', $this->tags) . "'";
        $q = "SELECT COUNT(*) AS count" . PHP_EOL
            . " FROM " . self::TRANLSATION_TABLE . PHP_EOL
            . " WHERE m_message REGEXP " . $regex . PHP_EOL
        ;
        $res = $this->db->query($q);
        if ($this->db->numRows($res) === 0) {
            return 0;
        }
        $row = $this->db->fetchAssoc($res);
        return (int) $row["count"];
    }

    /**
     * Gets the next dataset that contains any of the
     * tags in ilMailTemplateConvertHtmlToMarkdown::tags
     *
     * @returns [string|int]
     */
    protected function getNextObject(): array
    {
        if (!$this->db->tableExists(self::TRANLSATION_TABLE)) {
            return [];
        }
        $regex = "'" . implode('|', $this->tags) . "'";
        $q = "SELECT tpl_id, lang, m_message" . PHP_EOL
            . " FROM " . self::TRANLSATION_TABLE . PHP_EOL
            . " WHERE m_message REGEXP " . $regex . PHP_EOL
            . " ORDER BY tpl_id ASC, lang ASC" . PHP_EOL
            . " LIMIT 1" . PHP_EOL
        ;
        $res = $this->db->query($q);
        if ($this->db->numRows($res) === 0) {
            return [];
        }
        $row = $this->db->fetchAssoc($res);
        return [
            'tpl_id' => (int) $row["tpl_id"],
            'lang' => (string) $row["lang"],
            'm_message' => (string) $row["m_message"]
        ];
    }


    /**
     * Converts all HTML tags in ilMailTemplateConvertHtmlToMarkdown::tags to markdown
     * and removes all other HTML tags
     */
    protected function convert(string $html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $options = LIBXML_HTML_NOIMPLIED + LIBXML_HTML_NODEFDTD + LIBXML_NOXMLDECL;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', $options); // force wrap in DIV to avoid additional P tags

        // Paragraphs
        while (count($dom->getElementsByTagName('p')) > 0) {
            $paragraph = $dom->getElementsByTagName('p')->item(0);
            $paragraph->parentNode->replaceChild(
                new \DOMText("\n" . $this->getContent($paragraph) . "\n"),
                $paragraph
            );
        }

        // Line breaks
        while (count($dom->getElementsByTagName('br')) > 0) {
            $break = $dom->getElementsByTagName('br')->item(0);
            $break->parentNode->replaceChild(
                new \DOMText("\n"),
                $break
            );
        }

        // Single tags
        $tags = [
            'b' => '**',
            'strong' => '**',
            'i' => '_',
            'em' => '_',
            'u' => '--'
        ];
        foreach ($tags as $search => $replace) {
            while (count($dom->getElementsByTagName($search)) > 0) {
                $tag = $dom->getElementsByTagName($search)->item(0);
                $tag->parentNode->replaceChild(
                    new \DOMText($replace . trim($this->getContent($tag)) . $replace),
                    $tag
                );
            }
        }

        // Header
        $tags = [
            'h1' => '#',
            'h2' => '##',
            'h3' => '###',
            'h4' => '####',
            'h5' => '#####',
            'h6' => '######'
        ];
        foreach ($tags as $search => $replace) {
            while (count($dom->getElementsByTagName($search)) > 0) {
                $tag = $dom->getElementsByTagName($search)->item(0);
                $tag->parentNode->replaceChild(
                    new \DOMText("\n" . $replace . ' ' . trim($this->getContent($tag)) . "\n"),
                    $tag
                );
            }
        }

        // Unordered lists
        while (count($dom->getElementsByTagName('ul')) > 0) {
            $ul = $dom->getElementsByTagName('ul')->item(0);

            while (count($ul->getElementsByTagName('li')) > 0) {
                $li = $ul->getElementsByTagName('li')->item(0);
                $li->parentNode->replaceChild(
                    new \DOMText("\n" . '- ' . $this->getContent($li)),
                    $li
                );
            }

            $ul->parentNode->replaceChild(
                new \DOMText("\n" . $this->remove_empty_lines($ul->textContent) . "\n"),
                $ul
            );
        }

        // Ordered lists
        while (count($dom->getElementsByTagName('ol')) > 0) {
            $ol = $dom->getElementsByTagName('ol')->item(0);

            $counter = 1;
            while (count($ol->getElementsByTagName('li')) > 0) {
                $li = $ol->getElementsByTagName('li')->item(0);
                $li->parentNode->replaceChild(
                    new \DOMText("\n" . $counter++ . '. ' . $this->getContent($li)),
                    $li
                );
            }

            $ol->parentNode->replaceChild(
                new \DOMText("\n" . $this->remove_empty_lines($ol->textContent) . "\n"),
                $ol
            );
        }

        // Links
        while (count($dom->getElementsByTagName('a')) > 0) {
            $link = $dom->getElementsByTagName('a')->item(0);
            $link_text = $this->getContent($link);
            $link_href = ($link->attributes->getNamedItem('href') !== null)
                ? $link->attributes->getNamedItem('href')->textContent
                : '';
            $link_title = ($link->attributes->getNamedItem('title') !== null)
                ? $link->attributes->getNamedItem('title')->textContent
                : '';

            $replace = '[' . trim($link_text) . '](' . trim($link_href) ;
            if ($link_title !== '') {
                $replace .= ' "' . trim(str_replace('"', '\"', $link_title)) . '"';
            }
            $replace .= ')';

            $link->parentNode->replaceChild(
                new \DOMText($replace),
                $link
            );
        }

        // Images
        while (count($dom->getElementsByTagName('img')) > 0) {
            $image = $dom->getElementsByTagName('img')->item(0);
            $image_src = ($image->attributes->getNamedItem('src') !== null)
                ? $image->attributes->getNamedItem('src')->textContent
                : '';
            $image_alt = ($image->attributes->getNamedItem('alt') !== null)
                ? $image->attributes->getNamedItem('alt')->textContent
                : '';
            $image_title = ($image->attributes->getNamedItem('title') !== null)
                ? $image->attributes->getNamedItem('title')->textContent
                : '';

            $replace = '![' . trim($image_alt) . '](' . trim($image_src) ;
            if ($image_title !== '') {
                $replace .= ' "' . trim(str_replace('"', '\"', $image_title)) . '"';
            }
            $replace .= ')';

            $image->parentNode->replaceChild(
                new \DOMText($replace),
                $image
            );
        }

        $inner_html = ''; // remove surrounding DIV tags by only using inner HTML
        foreach ($dom->documentElement->childNodes as $child) {
            $inner_html .= $dom->saveHTML($child);
        }

        return strip_tags($inner_html); // remove all other tags
    }

    /**
     * Gets text content of a DOMNode
     * Recursively calls ilMailTemplateConvertHtmlToMarkdown::convert() for nodes with child HTML elements
     */
    public function getContent(\DOMNode $node): string
    {
        $hasChildren = false;
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $hasChildren = true;
            }
        }

        if ($hasChildren === false) {
            return $node->textContent;
        }
        return $this->convert($this->innerHTML($node, false));
    }

    /**
     * Remove empty lines in lists for better readability
     */
    protected function remove_empty_lines($string): string
    {
        $lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $string));
        $lines = array_filter($lines, function ($value) {
            return $value !== '';
        });
        return implode("\n", $lines) . "\n";
    }

    /**
     * Get the inner HTML of a DOMNode
     */
    protected function innerHTML(\DOMNode $n, $include_target_tag = true): string
    {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($n, true));
        $html = trim($doc->saveHTML());
        if ($include_target_tag) {
            return $html;
        }
        return preg_replace('@^<' . $n->nodeName . '[^>]*>|</' . $n->nodeName . '>$@', '', $html);
    }
}
