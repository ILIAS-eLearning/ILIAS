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

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilMDCopyrightMigration implements Setup\Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "Migration of available copyrights.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $res = $this->db->query(
            'SELECT entry_id, title, copyright FROM il_md_cpr_selections WHERE migrated = 0 LIMIT 1'
        );

        if ($row = $this->db->fetchAssoc($res)) {
            if (!isset($row['entry_id'])) {
                return;
            }
            $fields = $this->extractFields(
                (string) ($row['title'] ?? ''),
                (string) ($row['copyright'] ?? '')
            );
            $fields['migrated'] = [\ilDBConstants::T_INTEGER, 1];
            $this->db->update(
                'il_md_cpr_selections',
                $fields,
                ['entry_id' => [\ilDBConstants::T_INTEGER, (int) $row['entry_id']]]
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $res = $this->db->query(
            'SELECT COUNT(*) AS count FROM il_md_cpr_selections WHERE migrated = 0'
        );

        $row = $this->db->fetchAssoc($res);
        return (int) $row['count'];
    }

    protected function extractFields(
        string $title,
        string $copyright
    ): array {
        $full_name = '';
        $link = '';
        $image_link = '';
        $alt_text = '';

        //find the image
        if (preg_match('/<\s*img((?:.|\n)*?)\/>/i', $copyright, $img_matches)) {
            if (preg_match('/src\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $img_matches[1], $src_matches)) {
                $image_link = strip_tags($src_matches[1]);
            }
            if (preg_match('/alt\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $img_matches[1], $alt_matches)) {
                $alt_text = strip_tags($alt_matches[1]);
            }
        }

        //find the link
        if (preg_match('/<\s*a((?:.|\n)[^<]*?)<\s*\/a>/i', $copyright, $link_matches)) {
            if (preg_match('/href\s*=\s*(?:"|\')(.*?)(?:"|\')/i', $link_matches[1], $name_matches)) {
                $link = strip_tags($name_matches[1]);
            }
            if (preg_match('/>((?:\n|.)*)/i', $link_matches[1], $href_matches)) {
                $full_name = strip_tags($href_matches[1]);
            }
        } else {
            $full_name = strip_tags($copyright);
        }

        $image_link = $this->translatePreInstalledLinksToSVG($image_link);
        $full_name = $this->cleanupPreInstalledPublicDomainFullName($full_name);
        $title = $this->updatePreInstalledTitles($link, $title);

        return [
            'title' => [\ilDBConstants::T_TEXT, $title],
            'full_name' => [\ilDBConstants::T_TEXT, $full_name],
            'link' => [\ilDBConstants::T_TEXT, $link],
            'image_link' => [\ilDBConstants::T_TEXT, $image_link],
            'alt_text' => [\ilDBConstants::T_TEXT, $alt_text]
        ];
    }

    protected function translatePreInstalledLinksToSVG(string $image_link): string
    {
        $mapping = [
            // 4.0
            'https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-nd.svg',
            'https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-sa.svg',
            'https://i.creativecommons.org/l/by-nc/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc.svg',
            'https://i.creativecommons.org/l/by-nd/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nd.svg',
            'https://i.creativecommons.org/l/by-sa/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-sa.svg',
            'https://i.creativecommons.org/l/by/4.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by.svg',
            // 3.0
            'http://i.creativecommons.org/l/by-nc-nd/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-nd.svg',
            'http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc-sa.svg',
            'http://i.creativecommons.org/l/by-nc/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nc.svg',
            'http://i.creativecommons.org/l/by-nd/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-nd.svg',
            'http://i.creativecommons.org/l/by-sa/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by-sa.svg',
            'http://i.creativecommons.org/l/by/3.0/88x31.png' => 'https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/by.svg'
        ];

        if (key_exists($image_link, $mapping)) {
            return $mapping[$image_link];
        }
        return $image_link;
    }

    /**
     * see https://mantis.ilias.de/view.php?id=41301
     */
    protected function updatePreInstalledTitles(string $link, string $title): string
    {
        $old_titles_by_link = [
            'http://creativecommons.org/licenses/by-nc-nd/4.0/' => 'Attribution Non-commercial No Derivatives (by-nc-nd)',
            'http://creativecommons.org/licenses/by-nc-sa/4.0/' => 'Attribution Non-commercial Share Alike (by-nc-sa)',
            'http://creativecommons.org/licenses/by-nc/4.0/' => 'Attribution Non-commercial (by-nc)',
            'http://creativecommons.org/licenses/by-nd/4.0/' => 'Attribution No Derivatives (by-nd)',
            'http://creativecommons.org/licenses/by-sa/4.0/' => 'Attribution Share Alike (by-sa)',
            'http://creativecommons.org/licenses/by/4.0/' => 'Attribution (by)'
        ];
        $new_titles_by_link = [
            'http://creativecommons.org/licenses/by-nc-nd/4.0/' => 'Attribution Non-commercial No Derivatives (BY-NC-ND) 4.0',
            'http://creativecommons.org/licenses/by-nc-sa/4.0/' => 'Attribution Non-commercial Share Alike (BY-NC-SA) 4.0',
            'http://creativecommons.org/licenses/by-nc/4.0/' => 'Attribution Non-commercial (BY-NC) 4.0',
            'http://creativecommons.org/licenses/by-nd/4.0/' => 'Attribution No Derivatives (BY-ND) 4.0',
            'http://creativecommons.org/licenses/by-sa/4.0/' => 'Attribution Share Alike (BY-SA) 4.0',
            'http://creativecommons.org/licenses/by/4.0/' => 'Attribution (BY) 4.0'
        ];
        if (
            key_exists($link, $old_titles_by_link) &&
            $old_titles_by_link[$link] === $title &&
            key_exists($link, $new_titles_by_link)
        ) {
            return $new_titles_by_link[$link];
        }
        return $title;
    }

    /**
     * see https://mantis.ilias.de/view.php?id=41301
     */
    protected function cleanupPreInstalledPublicDomainFullName(string $full_name): string
    {
        if ($full_name === 'This work has all rights reserved by the owner.') {
            return 'All rights reserved';
        }
        return $full_name;
    }
}
