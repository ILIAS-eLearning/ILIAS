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

use ILIAS\Notes\Service;
use ILIAS\Blog\ReadingTime\ReadingTimeManager;
use ILIAS\Blog\Settings\SettingsManager;
use ILIAS\Blog\InternalService;

/**
 * Blog Data set class
 * This class implements the following entities:
 * - blog: object data
 * - blog_posting: data from table il_blog_posting
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogDataSet extends ilDataSet
{
    protected InternalService $service;
    protected SettingsManager $blog_settings;
    protected ReadingTimeManager $reading_time;
    protected Service $notes;
    protected ilObjBlog $current_blog;
    public static array $style_map = array();
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
        $this->notes = $DIC->notes();
        $this->reading_time = $DIC->blog()->internal()->domain()->readingTime();
        $this->blog_settings = $DIC->blog()->internal()->domain()->blogSettings();
        $this->service = $DIC->blog()->internal();
    }

    public function getSupportedVersions(): array
    {
        return array("4.3.0", "5.0.0", "5.3.0", "8.0");
    }

    protected function getXmlNamespace(
        string $a_entity,
        string $a_schema_version
    ): string {

        if ($a_entity === 'blog' || $a_entity == 'blog_posting') {
            // they share the same xsd, therfore the same namespace
            return "http://www.ilias.de/xml/Modules/Blog/blog";
        }
        return "http://www.ilias.de/xml/Modules/Blog/" . $a_entity;
    }

    protected function getTypes(
        string $a_entity,
        string $a_version
    ): array {
        if ($a_entity === "blog") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory"
                    );

                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory",
                        "AbsShorten" => "integer",
                        "AbsShortenLen" => "integer",
                        "AbsImage" => "integer",
                        "AbsImgWidth" => "integer",
                        "AbsImgHeight" => "integer",
                        "NavMode" => "integer",
                        "NavListPost" => "integer",
                        "NavListMon" => "integer",
                        "Keywords" => "integer",
                        "Authors" => "integer",
                        "NavOrder" => "text",
                        "OvPost" => "integer",
                        "Style" => "integer"
                    );

                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory",
                        "AbsShorten" => "integer",
                        "AbsShortenLen" => "integer",
                        "AbsImage" => "integer",
                        "AbsImgWidth" => "integer",
                        "AbsImgHeight" => "integer",
                        "NavMode" => "integer",
                        "NavListMonWithPost" => "integer",
                        "NavListMon" => "integer",
                        "Keywords" => "integer",
                        "Authors" => "integer",
                        "NavOrder" => "text",
                        "OvPost" => "integer",
                        "Style" => "integer"
                    );

                case "8.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Notes" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "RssActive" => "integer",
                        "Approval" => "integer",
                        "Dir" => "directory",
                        "AbsShorten" => "integer",
                        "AbsShortenLen" => "integer",
                        "AbsImage" => "integer",
                        "AbsImgWidth" => "integer",
                        "AbsImgHeight" => "integer",
                        "NavMode" => "integer",
                        "NavListMonWithPost" => "integer",
                        "NavListMon" => "integer",
                        "Keywords" => "integer",
                        "Authors" => "integer",
                        "NavOrder" => "text",
                        "OvPost" => "integer",
                        "Style" => "integer",
                        "ReadingTime" => "integer"
                    );
            }
        }

        if ($a_entity === "blog_posting") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "5.3.0":
                case "8.0":
                    return array(
                        "Id" => "integer",
                        "BlogId" => "integer",
                        "Title" => "integer",
                        "Created" => "text",
                        "Author" => "text",
                        "Approved" => "integer",
                        "LastWithdrawn" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(
        string $a_entity,
        string $a_version,
        array $a_ids
    ): void {
        $ilDB = $this->db;

        if ($a_entity === "blog") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery(
                        "SELECT bl.id,od.title,od.description," .
                        "bl.notes,bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text")
                    );
                    break;

                case "5.0.0":
                    $this->getDirectDataFromQuery(
                        "SELECT bl.id,od.title,od.description," .
                        "bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval," .
                        "bl.abs_shorten,bl.abs_shorten_len,bl.abs_image,bl.abs_img_width,bl.abs_img_height," .
                        "bl.nav_mode,bl.nav_list_post,bl.nav_list_mon,bl.keywords,bl.authors,bl.nav_order," .
                        "bl.ov_post" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text")
                    );
                    break;

                case "5.3.0":
                    $this->getDirectDataFromQuery(
                        "SELECT bl.id,od.title,od.description," .
                        "bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval," .
                        "bl.abs_shorten,bl.abs_shorten_len,bl.abs_image,bl.abs_img_width,bl.abs_img_height," .
                        "bl.nav_mode,bl.nav_list_mon_with_post,bl.nav_list_mon,bl.keywords,bl.authors,bl.nav_order," .
                        "bl.ov_post" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text")
                    );
                    break;

                case "8.0":
                    $this->getDirectDataFromQuery(
                        "SELECT bl.id,od.title,od.description," .
                        "bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval," .
                        "bl.abs_shorten,bl.abs_shorten_len,bl.abs_image,bl.abs_img_width,bl.abs_img_height," .
                        "bl.nav_mode,bl.nav_list_mon_with_post,bl.nav_list_mon,bl.keywords,bl.authors,bl.nav_order," .
                        "bl.ov_post" .
                        " FROM il_blog bl" .
                        " JOIN object_data od ON (od.obj_id = bl.id)" .
                        " WHERE " . $ilDB->in("bl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("blog", "text")
                    );
                    foreach ($this->data as $idx => $item) {
                        $this->data[$idx]["ReadingTime"] = (int) $this->reading_time->isActivated((int) $item["Id"]);
                    }
                    break;
            }
        }

        if ($a_entity === "blog_posting") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "5.3.0":
                case "8.0":
                    $this->getDirectDataFromQuery(
                        "SELECT id,blog_id,title,created,author,approved,last_withdrawn" .
                        " FROM il_blog_posting WHERE " .
                        $ilDB->in("blog_id", $a_ids, false, "integer")
                    );
                    foreach ($this->data as $idx => $item) {
                        // create full export id
                        $this->data[$idx]["Author"] = $this->createObjectExportId("usr", (string) $item["Author"]);
                    }
                    break;
            }

            // keywords
            foreach ($this->data as $idx => $item) {
                $blog_id = ilBlogPosting::lookupBlogId($item["Id"]);
                $keywords = ilBlogPosting::getKeywords($blog_id, $item["Id"]);
                if ($keywords) {
                    foreach ($keywords as $kidx => $keyword) {
                        $this->data[$idx]["Keyword" . $kidx] = $keyword;
                    }
                }
            }
        }
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        if ($a_entity === "blog") {
            return array(
                "blog_posting" => array("ids" => $a_rec["Id"] ?? null)
            );
        }
        return [];
    }

    public function getXmlRecord(
        string $a_entity,
        string $a_version,
        array $a_set
    ): array {
        if ($a_entity === "blog") {
            $style = $this->content_style_domain->styleForObjId((int) $a_set["Id"]);

            $a_set["Style"] = $style->getStyleId();

            // #14734
            $a_set["Notes"] = $this->notes->domain()->commentsActive((int) $a_set["Id"]);
        }

        return $a_set;
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        $data = $this->service->data();
        $a_rec = $this->stripTags($a_rec);
        switch ($a_entity) {
            case "blog":

                // container copy
                if ($new_id = $a_mapping->getMapping("components/ILIAS/Container", "objs", $a_rec["Id"])) {
                    $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
                } else {
                    $newObj = new ilObjBlog();
                    $newObj->create();
                }

                $newObj->setTitle($a_rec["Title"] ?? "");
                $newObj->setDescription($a_rec["Description"] ?? "");
                $newObj->setNotesStatus((bool) ($a_rec["Notes"] ?? false));
                $newObj->update();

                $blog_settings = $data->settings(
                    $newObj->getId(),
                    (bool) ($a_rec["Ppic"] ?? false),
                    $a_rec["BgColor"] ?? "",
                    $a_rec["FontColor"] ?? "",
                    (bool) ($a_rec["RssActive"] ?? false),
                    (bool) ($a_rec["Approval"] ?? false),
                    (bool) ($a_rec["AbsShorten"] ?? false),
                    (int) ($a_rec["AbsShortenLen"] ?? 0),
                    (bool) ($a_rec["AbsImage"] ?? 0),
                    (int) ($a_rec["AbsImgWidth"] ?? 0),
                    (int) ($a_rec["AbsImgHeight"] ?? 0),
                    (bool) ($a_rec["Keywords"] ?? false),
                    (bool) ($a_rec["Authors"] ?? false),
                    (int) ($a_rec["NavMode"] ?? 0),
                    (($a_rec["NavListMonWithPost"] ?? 0) == 0)
                        ? 3
                        : (int) $a_rec["NavListMonWithPost"],
                    (int) ($a_rec["NavListMon"] ?? 3),
                    (int) ($a_rec["OvPost"] ?? null),
                    trim($a_rec["NavOrder"])
                        ? explode(";", $a_rec["NavOrder"])
                        : []
                );
                $this->blog_settings->update($blog_settings);

                if ($a_rec["Style"] ?? false) {
                    self::$style_map[$a_rec["Style"]][] = $newObj->getId();
                }

                // reading time
                $this->reading_time->activate($newObj->getId(), (bool) ($a_rec["ReadingTime"] ?? false));

                $a_mapping->addMapping("components/ILIAS/Blog", "blog", $a_rec["Id"], (string) $newObj->getId());
                $a_mapping->addMapping(
                    'components/ILIAS/MetaData',
                    'md',
                    $a_rec["Id"] . ':0:blog',
                    $newObj->getId() . ':0:blog'
                );
                break;

            case "blog_posting":
                $blog_id = (int) $a_mapping->getMapping("components/ILIAS/Blog", "blog", $a_rec["BlogId"]);
                if ($blog_id) {
                    $newObj = new ilBlogPosting();
                    $newObj->setBlogId($blog_id);
                    $newObj->setTitle($a_rec["Title"] ?? "");
                    $newObj->setCreated(new ilDateTime($a_rec["Created"] ?? null, IL_CAL_DATETIME));
                    $newObj->setApproved((bool) ($a_rec["Approved"] ?? null));
                    $newObj->setWithdrawn(new ilDateTime($a_rec["LastWithdrawn"] ?? null, IL_CAL_DATETIME));

                    // parse export id into local id (if possible)
                    $author = $this->parseObjectExportId($a_rec["Author"] ?? "", "-1");
                    $newObj->setAuthor((int) $author["id"]);

                    $newObj->create(true);

                    // keywords
                    $keywords = array();
                    for ($loop = 0; $loop < 1000; $loop++) {
                        $idx = "Keyword" . $loop;
                        if (isset($a_rec[$idx])) {
                            $keyword = trim($a_rec[$idx]);
                            if ($keyword !== '') {
                                $keywords[] = $keyword;
                            }
                        }
                    }
                    if (count($keywords)) {
                        $newObj->updateKeywords($keywords);
                    }

                    $a_mapping->addMapping(
                        "components/ILIAS/COPage",
                        "pg",
                        "blp:" . $a_rec["Id"],
                        "blp:" . $newObj->getId()
                    );
                }
                break;
        }
    }
}
