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

namespace ILIAS\Blog\Export;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalRepoService;
use ILIAS\Blog\InternalDomainService;

class ExportManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function isCommentsExportPossible(int $blog_id): bool
    {
        $setting = $this->domain->settings();
        $notes = $this->domain->notes();
        $privacy = \ilPrivacySettings::getInstance();

        if ($setting->get("disable_comments")) {
            return false;
        }
        if (!$privacy->enabledCommentsExport()) {
            return false;
        }
        if (!$notes->commentsActive($blog_id)) {
            return false;
        }
        return true;
    }

    /**
     * Build export
     */
    public function buildHtml(
        int $node_id,
        int $owner_id,
        string $format,
        bool $is_repository,
        bool $a_include_comments = false,
        bool $print_version = false
    ): BlogHtmlExport {
        $format = explode("_", $format);
        if (($format[1] ?? "") === "comments" || $a_include_comments) {
            $a_include_comments = true;
        }

        if ($is_repository) {
            $blog_id = \ilObject::_lookupObjId($node_id);
        } else {
            $blog_id = $this->domain->getObjectIdForWspId($node_id);
        }

        $subdir = "blog_" . $blog_id;
        if ($print_version) {
            $subdir .= "print";
        }

        $blog_export = new BlogHtmlExport(
            $node_id,
            $owner_id,
            $is_repository,
            "",
            $subdir
        );
        $blog_export->setPrintVersion($print_version);
        $blog_export->includeComments($a_include_comments);
        $blog_export->exportHTML();
        return $blog_export;
    }

    public function buildExportLink(
        string $a_template,
        string $a_type,
        string $a_id,
        array $keywords
    ): string {
        switch ($a_type) {
            case "list":
                $a_type = "m";
                break;

            case "keyword":
                $map = array_flip(array_keys($keywords));
                $a_id = (string) ($map[$a_id] ?? "");
                $a_type = "k";
                break;

            default:
                $a_type = "p";
                break;
        }

        return str_replace(array("{TYPE}", "{ID}"), array($a_type, $a_id), $a_template);
    }


}
