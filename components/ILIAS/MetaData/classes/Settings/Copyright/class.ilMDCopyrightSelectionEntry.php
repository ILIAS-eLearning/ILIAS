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

use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\MetaData\Copyright\Renderer;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @deprecated use ILIAS/MetaData/Copyright/DatabaseRepository
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilMDCopyrightSelectionEntry
{
    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected RendererInterface $renderer;
    protected RepositoryInterface $repository;
    protected UIRenderer $ui_renderer;

    private int $entry_id;
    private string $title = '';
    private string $description = '';
    private string $copyright = '';
    private int $usage = 0;

    protected bool $outdated = false;

    protected int $order_position = 0;

    public function __construct(int $a_entry_id)
    {
        global $DIC;

        $this->renderer = new Renderer(
            $DIC->ui()->factory(),
            $DIC->resourceStorage()
        );
        $this->repository = new DatabaseRepository($DIC->database());
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->logger = $DIC->logger()->meta();
        $this->db = $DIC->database();
        $this->entry_id = $a_entry_id;
        $this->read();
    }

    /**
     * @return ilMDCopyrightSelectionEntry[]
     */
    public static function _getEntries(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT entry_id FROM il_md_cpr_selections ORDER BY is_default DESC, position ASC";
        $res = $ilDB->query($query);

        $entries = [];
        while ($row = $ilDB->fetchObject($res)) {
            $entries[] = new ilMDCopyrightSelectionEntry((int) $row->entry_id);
        }
        return $entries;
    }

    public static function lookupCopyyrightTitle(string $a_cp_string): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }

        $query = "SELECT title FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $ilDB->quote($entry_id, ilDBConstants::T_INTEGER) . " ";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        return $row->title ?? '';
    }

    public static function _lookupCopyright(
        string $a_cp_string,
        bool $for_export = false
    ): string {
        global $DIC;

        $renderer = new Renderer(
            $DIC->ui()->factory(),
            $DIC->resourceStorage()
        );
        $repository = new DatabaseRepository($DIC->database());
        $ui_renderer = $DIC->ui()->renderer();

        if (!$entry_id = self::_extractEntryId($a_cp_string)) {
            return $a_cp_string;
        }

        $entry = $repository->getEntry($entry_id);
        $components = $renderer->toUIComponents($entry->copyrightData());

        /*
         * Take icon out of the render if it comes from a file, to avoid
         * file delivery links showing up in export files.
         */
        if ($for_export && !$entry->copyrightData()->isImageLink()) {
            foreach ($components as $id => $component) {
                if ($component instanceof Icon) {
                    unset($components[$id]);
                }
            }
        }

        return $ui_renderer->render($components);
    }

    public static function lookupCopyrightByText(string $copyright_text): int
    {
        global $DIC;

        $db = $DIC->database();
        $full_name = '';
        $link = '';
        $image_link = '';
        $alt_text = '';

        //find the image
        if (preg_match('/<\s*img((?:.|\n)*?)\/>/i', $copyright_text, $img_matches)) {
            if (preg_match('/src\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $img_matches[1], $src_matches)) {
                $image_link = strip_tags($src_matches[1]);
            }
            if (preg_match('/alt\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $img_matches[1], $alt_matches)) {
                $alt_text = strip_tags($alt_matches[1]);
            }
        }

        //find the link
        if (preg_match('/<\s*a((?:.|\n)[^<]*?)<\s*\/a>/i', $copyright_text, $link_matches)) {
            if (preg_match('/href\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $link_matches[1], $name_matches)) {
                $link = strip_tags($name_matches[1]);
            }
            if (preg_match('/>((?:\n|.)*)/i', $link_matches[1], $href_matches)) {
                $full_name = strip_tags($href_matches[1]);
            }
        } else {
            $full_name = strip_tags($copyright_text);
        }

        /*
         * Since the icon is not exported if it comes from a file, we have to be a bit
         * more lenient with the matching if it's not there.
         */
        if ($image_link === '' && $alt_text === '') {
            $query = 'SELECT entry_id FROM il_md_cpr_selections ' .
                'WHERE full_name = ' . $db->quote($full_name, ilDBConstants::T_TEXT) .
                ' AND link = ' . $db->quote($link, ilDBConstants::T_TEXT);
        } else {
            $query = 'SELECT entry_id FROM il_md_cpr_selections ' .
                'WHERE full_name = ' . $db->quote($full_name, ilDBConstants::T_TEXT) .
                ' AND link = ' . $db->quote($link, ilDBConstants::T_TEXT) .
                ' AND image_link = ' . $db->quote($image_link, ilDBConstants::T_TEXT) .
                ' AND alt_text = ' . $db->quote($alt_text, ilDBConstants::T_TEXT);
        }
        $res = $db->query($query);
        while ($row = $db->fetchObject($res)) {
            return (int) $row->entry_id;
        }
        return 0;
    }

    public static function _extractEntryId(string $a_cp_string): int
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $a_cp_string, $matches)) {
            return 0;
        }
        if ($matches[1] != IL_INST_ID) {
            return 0;
        }
        return (int) ($matches[2] ?? 0);
    }

    public static function isEntry($a_cp_string): bool
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $a_cp_string)) {
            return false;
        }
        return true;
    }

    public function getUsage(): int
    {
        return $this->usage;
    }

    public function getEntryId(): int
    {
        return $this->entry_id;
    }

    /**
     * Get if the entry is default
     * No setter for this.
     */
    public function getIsDefault(): bool
    {
        $query = "SELECT is_default FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->entry_id, 'integer');

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return (bool) ($row['is_default'] ?? false);
    }

    public function setOutdated(bool $a_value): void
    {
        $this->outdated = $a_value;
    }

    public function getOutdated(): bool
    {
        return $this->outdated;
    }

    public static function getDefault(): int
    {
        global $DIC;

        $db = $DIC->database();

        $query = "SELECT entry_id FROM il_md_cpr_selections " .
            "WHERE is_default = " . $db->quote(1, 'integer');

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return (int) $row['entry_id'];
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_desc): void
    {
        $this->description = $a_desc;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setCopyright(string $a_copyright): void
    {
        $this->copyright = $a_copyright;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function setOrderPosition(int $a_position): void
    {
        $this->order_position = $a_position;
    }

    public function getOrderPosition(): int
    {
        return $this->order_position;
    }

    protected function getNextOrderPosition(): int
    {
        $query = "SELECT count(entry_id) total FROM il_md_cpr_selections";
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return $row['total'] + 1;
    }

    public function add(): bool
    {
        $next_id = $this->db->nextId('il_md_cpr_selections');

        $this->db->insert('il_md_cpr_selections', array(
            'entry_id' => array('integer', $next_id),
            'title' => array('text', $this->getTitle()),
            'description' => array('clob', $this->getDescription()),
            //'copyright' => array('clob', $this->getCopyright()),
            'outdated' => array('integer', $this->getOutdated()),
            'position' => array('integer', $this->getNextOrderPosition())
        ));
        $this->entry_id = $next_id;
        return true;
    }

    public function update(): bool
    {
        $this->db->update('il_md_cpr_selections', array(
            'title' => array('text', $this->getTitle()),
            'description' => array('clob', $this->getDescription()),
            //'copyright' => array('clob', $this->getCopyright()),
            'outdated' => array('integer', $this->getOutdated()),
            'position' => array('integer', $this->getOrderPosition())
        ), array(
            'entry_id' => array('integer', $this->getEntryId())
        ));
        return true;
    }

    public function delete(): void
    {
        /*$query = "DELETE FROM il_md_cpr_selections " .
            "WHERE entry_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $this->db->manipulate($query);*/
    }

    public function validate(): bool
    {
        return $this->getTitle() !== '';
    }

    private function read(): void
    {
        $entry = $this->repository->getEntry($this->entry_id);

        $rendered_cp = $this->ui_renderer->render(
            $this->renderer->toUIComponents($entry->copyrightData())
        );

        $this->setTitle($entry->title());
        $this->setDescription($entry->description());
        $this->setCopyright($rendered_cp);
        $this->setOutdated($entry->isOutdated());
        $this->setOrderPosition($entry->position());

        $query = "SELECT count(meta_rights_id) used FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote(
                'il_copyright_entry__' . IL_INST_ID . '__' . $this->getEntryId(),
                'text'
            );

        $res = $this->db->query($query);
        $row = $this->db->fetchObject($res);
        $this->usage = (int) $row->used;
    }

    public static function createIdentifier(int $a_entry_id): string
    {
        return 'il_copyright_entry__' . IL_INST_ID . '__' . $a_entry_id;
    }
}
