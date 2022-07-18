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
 * Class ilObjBlog
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlog extends ilObject2
{
    public const NAV_MODE_LIST = 1;
    public const NAV_MODE_MONTH = 2;
    public const ABSTRACT_DEFAULT_SHORTEN_LENGTH = 500;
    public const ABSTRACT_DEFAULT_IMAGE_WIDTH = 144;
    public const ABSTRACT_DEFAULT_IMAGE_HEIGHT = 144;
    public const NAV_MODE_LIST_DEFAULT_POSTINGS = 10;
    protected \ILIAS\Notes\Service $notes_service;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_service;

    protected int $nav_mode_list_months_with_post = 0;
    protected bool $notes;
    protected string $bg_color;
    protected string $font_color;
    protected string $img;
    protected string $ppic;
    protected bool $rss;
    protected bool $approval;
    protected bool $style;
    protected bool $abstract_shorten = false;
    protected int $abstract_shorten_length = self::ABSTRACT_DEFAULT_SHORTEN_LENGTH;
    protected bool $abstract_image = false;
    protected int $abstract_image_width = self::ABSTRACT_DEFAULT_IMAGE_WIDTH;
    protected int $abstract_image_height = self::ABSTRACT_DEFAULT_IMAGE_HEIGHT;
    protected bool $keywords = true;
    protected int $nav_mode = self::NAV_MODE_LIST;
    protected int $nav_mode_list_postings = self::NAV_MODE_LIST_DEFAULT_POSTINGS;
    protected ?int $nav_mode_list_months;
    protected int $overview_postings = 5;
    protected bool $authors = true;
    protected array $order = [];

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        $this->notes_service = $DIC->notes();
        parent::__construct($a_id, $a_reference);
        $this->rbac_review = $DIC->rbac()->review();

        $this->content_style_service = $DIC
            ->contentStyle()
            ->domain()
            ->styleForObjId($this->getId());
    }

    protected function initType() : void
    {
        $this->type = "blog";
    }

    protected function doRead() : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM il_blog" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        $this->setProfilePicture((bool) $row["ppic"]);
        $this->setBackgroundColor((string) $row["bg_color"]);
        $this->setFontColor((string) $row["font_color"]);
        $this->setImage((string) $row["img"]);
        $this->setRSS($row["rss_active"]);
        $this->setApproval($row["approval"]);
        $this->setAbstractShorten($row["abs_shorten"]);
        $this->setAbstractShortenLength($row["abs_shorten_len"]);
        $this->setAbstractImage($row["abs_image"]);
        $this->setAbstractImageWidth($row["abs_img_width"]);
        $this->setAbstractImageHeight($row["abs_img_height"]);
        $this->setKeywords($row["keywords"]);
        $this->setAuthors($row["authors"]);
        $this->setNavMode($row["nav_mode"]);
        $this->setNavModeListMonthsWithPostings((int) $row["nav_list_mon_with_post"]);
        $this->setNavModeListMonths($row["nav_list_mon"]);
        $this->setOverviewPostings($row["ov_post"]);
        if (trim($row["nav_order"])) {
            $this->setOrder(explode(";", $row["nav_order"]));
        }
        
        // #14661
        $this->setNotesStatus(
            $this->notes_service->domain()->commentsActive($this->id)
        );
    }

    protected function doCreate(bool $clone_mode = false) : void
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate("INSERT INTO il_blog (id,ppic,rss_active,approval" .
            ",abs_shorten,abs_shorten_len,abs_image,abs_img_width,abs_img_height" .
            ",keywords,authors,nav_mode,nav_list_mon_with_post,ov_post) VALUES (" .
            $ilDB->quote($this->id, "integer") . "," .
            $ilDB->quote(true, "integer") . "," .
            $ilDB->quote(true, "integer") . "," .
            $ilDB->quote(false, "integer") . "," .
            $ilDB->quote($this->hasAbstractShorten(), "integer") . "," .
            $ilDB->quote($this->getAbstractShortenLength(), "integer") . "," .
            $ilDB->quote($this->hasAbstractImage(), "integer") . "," .
            $ilDB->quote($this->getAbstractImageWidth(), "integer") . "," .
            $ilDB->quote($this->getAbstractImageHeight(), "integer") . "," .
            $ilDB->quote($this->hasKeywords(), "integer") . "," .
            $ilDB->quote($this->hasAuthors(), "integer") . "," .
            $ilDB->quote($this->getNavMode(), "integer") . "," .
            $ilDB->quote($this->getNavModeListMonthsWithPostings(), "integer") . "," .
            $ilDB->quote($this->getOverviewPostings(), "integer") .
            ")");
        
        // #14661
        $this->notes_service->domain()->activateComments($this->id);
    }
    
    protected function doDelete() : void
    {
        $ilDB = $this->db;
        
        $this->deleteImage();

        ilBlogPosting::deleteAllBlogPostings($this->id);
        
        // remove all notifications
        ilNotification::removeForObject(ilNotification::TYPE_BLOG, $this->id);

        $ilDB->manipulate("DELETE FROM il_blog" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }
    
    protected function doUpdate() : void
    {
        $ilDB = $this->db;
    
        if ($this->id) {
            $ilDB->manipulate("UPDATE il_blog" .
                    " SET ppic = " . $ilDB->quote($this->hasProfilePicture(), "integer") .
                    ",bg_color = " . $ilDB->quote($this->getBackgroundColor(), "text") .
                    ",font_color = " . $ilDB->quote($this->getFontColor(), "text") .
                    ",img = " . $ilDB->quote($this->getImage(), "text") .
                    ",rss_active = " . $ilDB->quote($this->hasRSS(), "integer") .
                    ",approval = " . $ilDB->quote($this->hasApproval(), "integer") .
                    ",abs_shorten = " . $ilDB->quote($this->hasAbstractShorten(), "integer") .
                    ",abs_shorten_len = " . $ilDB->quote($this->getAbstractShortenLength(), "integer") .
                    ",abs_image = " . $ilDB->quote($this->hasAbstractImage(), "integer") .
                    ",abs_img_width = " . $ilDB->quote($this->getAbstractImageWidth(), "integer") .
                    ",abs_img_height = " . $ilDB->quote($this->getAbstractImageHeight(), "integer") .
                    ",keywords = " . $ilDB->quote($this->hasKeywords(), "integer") .
                    ",authors = " . $ilDB->quote($this->hasAuthors(), "integer") .
                    ",nav_mode = " . $ilDB->quote($this->getNavMode(), "integer") .
                    ",nav_list_mon_with_post = " . $ilDB->quote($this->getNavModeListMonthsWithPostings(), "integer") .
                    ",nav_list_mon = " . $ilDB->quote($this->getNavModeListMonths(), "integer") .
                    ",ov_post = " . $ilDB->quote($this->getOverviewPostings(), "integer") .
                    ",nav_order = " . $ilDB->quote(implode(";", $this->getOrder()), "text") .
                    " WHERE id = " . $ilDB->quote($this->id, "integer"));
                        
            // #14661
            $this->notes_service->domain()->activateComments(
                $this->id,
                $this->getNotesStatus()
            );
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null) : void
    {
        assert($new_obj instanceof ilObjBlog);
        // banner?
        $img = $this->getImage();
        if ($img) {
            $new_obj->setImage($img);
            
            $source = self::initStorage($this->getId());
            $target = $new_obj->initStorage($new_obj->getId());
            
            copy($source . $img, $target . $img);
        }
        
        $new_obj->setNotesStatus($this->getNotesStatus());
        $new_obj->setProfilePicture($this->hasProfilePicture());
        $new_obj->setBackgroundColor($this->getBackgroundColor());
        $new_obj->setFontColor($this->getFontColor());
        $new_obj->setRSS($this->hasRSS());
        $new_obj->setApproval($this->hasApproval());
        $new_obj->setAbstractShorten($this->hasAbstractShorten());
        $new_obj->setAbstractShortenLength($this->getAbstractShortenLength());
        $new_obj->setAbstractImage($this->hasAbstractImage());
        $new_obj->setAbstractImageWidth($this->getAbstractImageWidth());
        $new_obj->setAbstractImageHeight($this->getAbstractImageHeight());
        $new_obj->update();
        
        // set/copy stylesheet
        $this->content_style_service->cloneTo($new_obj->getId());
    }
    
    public function getNotesStatus() : bool
    {
        return $this->notes;
    }

    public function setNotesStatus(bool $a_status) : void
    {
        $this->notes = $a_status;
    }
    
    public function hasProfilePicture() : bool
    {
        return $this->ppic;
    }

    public function setProfilePicture(bool $a_status) : void
    {
        $this->ppic = $a_status;
    }
    
    public function getBackgroundColor() : string
    {
        if (!$this->bg_color) {
            $this->bg_color = "ffffff";
        }
        return $this->bg_color;
    }

    public function setBackgroundColor(string $a_value) : void
    {
        $this->bg_color = $a_value;
    }
    
    public function getFontColor() : string
    {
        if (!$this->font_color) {
            $this->font_color = "505050";
        }
        return $this->font_color;
    }

    public function setFontColor(string $a_value) : void
    {
        $this->font_color = $a_value;
    }
    
    public function getImage() : string
    {
        return $this->img;
    }

    public function setImage(string $a_value) : void
    {
        $this->img = $a_value;
    }
    
    public function getImageFullPath(
        bool $a_as_thumb = false
    ) : string {
        if ($this->img) {
            $path = self::initStorage($this->id);
            if (!$a_as_thumb) {
                return $path . $this->img;
            }

            return $path . "thb_" . $this->img;
        }
        return "";
    }
    
    public function deleteImage() : void
    {
        if ($this->id) {
            $storage = new ilFSStorageBlog($this->id);
            $storage->delete();
            
            $this->setImage("");
            
            $this->handleQuotaUpdate();
        }
    }

    /**
     * Init file system storage
     */
    public static function initStorage(
        int $a_id,
        string $a_subdir = null
    ) : string {
        $storage = new ilFSStorageBlog($a_id);
        $storage->create();
        
        $path = $storage->getAbsolutePath() . "/";
        
        if ($a_subdir) {
            $path .= $a_subdir . "/";
            
            if (!is_dir($path) && !mkdir($path) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
                
        return $path;
    }
    
    /**
     * Upload new image file
     */
    public function uploadImage(array $a_upload) : bool
    {
        if (!$this->id) {
            return false;
        }
        
        $this->deleteImage();
        
        // #10074
        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $a_upload["name"]);
    
        $path = self::initStorage($this->id);
        $original = "org_" . $this->id . "_" . $clean_name;
        $thumb = "thb_" . $this->id . "_" . $clean_name;
        $processed = $this->id . "_" . $clean_name;

        if (ilFileUtils::moveUploadedFile($a_upload["tmp_name"], $original, $path . $original)) {
            chmod($path . $original, 0770);

            $blga_set = new ilSetting("blga");
            /* as banner height should overflow, we only handle width
            $dimensions = $blga_set->get("banner_width")."x".
                $blga_set->get("banner_height");
            */
            $dimensions = $blga_set->get("banner_width");
            
            // take quality 100 to avoid jpeg artefacts when uploading jpeg files
            // taking only frame [0] to avoid problems with animated gifs
            $original_file = ilShellUtil::escapeShellArg($path . $original);
            $thumb_file = ilShellUtil::escapeShellArg($path . $thumb);
            $processed_file = ilShellUtil::escapeShellArg($path . $processed);
            ilShellUtil::execConvert($original_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
            ilShellUtil::execConvert(
                $original_file . "[0] -geometry " . $dimensions . " -quality 100 JPEG:" . $processed_file
            );
            
            $this->setImage($processed);
            
            $this->handleQuotaUpdate();
            
            return true;
        }
        return false;
    }
        
    public function hasRSS() : bool
    {
        return $this->rss;
    }

    public function setRSS(bool $a_status) : void
    {
        $this->rss = $a_status;
    }
    
    public function hasApproval() : bool
    {
        return $this->approval;
    }

    public function setApproval(bool $a_status) : void
    {
        $this->approval = $a_status;
    }
    

    public function hasAbstractShorten() : bool
    {
        return $this->abstract_shorten;
    }
    
    public function setAbstractShorten(bool $a_value) : void
    {
        $this->abstract_shorten = $a_value;
    }
    
    public function getAbstractShortenLength() : int
    {
        return $this->abstract_shorten_length;
    }
    
    public function setAbstractShortenLength(int $a_value) : void
    {
        $this->abstract_shorten_length = $a_value;
    }
            
    public function hasAbstractImage() : bool
    {
        return $this->abstract_image;
    }
    
    public function setAbstractImage(bool $a_value) : void
    {
        $this->abstract_image = $a_value;
    }
    
    public function getAbstractImageWidth() : int
    {
        return $this->abstract_image_width;
    }
    
    public function setAbstractImageWidth(int $a_value) : void
    {
        $this->abstract_image_width = $a_value;
    }
    
    public function getAbstractImageHeight() : int
    {
        return $this->abstract_image_height;
    }
    
    public function setAbstractImageHeight(int $a_value) : void
    {
        $this->abstract_image_height = $a_value;
    }
    
    public function setKeywords(bool $a_value) : void
    {
        $this->keywords = $a_value;
    }
    
    public function hasKeywords() : bool
    {
        return $this->keywords;
    }
    
    public function setAuthors(bool $a_value) : void
    {
        $this->authors = $a_value;
    }
    
    public function hasAuthors() : bool
    {
        return $this->authors;
    }
    
    public function setNavMode(int $a_value) : void
    {
        if (in_array($a_value, array(self::NAV_MODE_LIST, self::NAV_MODE_MONTH))) {
            $this->nav_mode = $a_value;
        }
    }
    
    public function getNavMode() : int
    {
        return $this->nav_mode;
    }
    
    public function setNavModeListMonthsWithPostings(int $a_value) : void
    {
        $this->nav_mode_list_months_with_post = $a_value;
    }
    
    public function getNavModeListMonthsWithPostings() : int
    {
        return $this->nav_mode_list_months_with_post;
    }
    
    public function setNavModeListMonths(?int $a_value) : void
    {
        $this->nav_mode_list_months = $a_value;
    }
    
    public function getNavModeListMonths() : ?int
    {
        return $this->nav_mode_list_months;
    }
    
    public function setOverviewPostings(?int $a_value) : void
    {
        $this->overview_postings = $a_value;
    }
    
    public function getOverviewPostings() : ?int
    {
        return $this->overview_postings;
    }
    
    public function setOrder(array $a_values = []) : void
    {
        $this->order = $a_values;
    }
    
    public function getOrder() : array
    {
        return $this->order;
    }
        
    public static function sendNotification(
        string $a_action,
        bool $a_in_wsp,
        int $a_blog_node_id,
        int $a_posting_id,
        ?string $a_comment = null
    ) : void {
        global $DIC;

        $ilUser = $DIC->user();
        
        // get blog object id (repository or workspace)
        if ($a_in_wsp) {
            $tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
            $blog_obj_id = $tree->lookupObjectId($a_blog_node_id);
            $access_handler = new ilWorkspaceAccessHandler($tree);
        } else {
            $blog_obj_id = ilObject::_lookupObjId($a_blog_node_id);
            $access_handler = null;
        }
        if (!$blog_obj_id) {
            return;
        }
                
        $posting = new ilBlogPosting($a_posting_id);
                                
        // #11138
        $ignore_threshold = ($a_action === "comment");
        
        $admin_only = false;
        
        // approval handling
        if (!$posting->isApproved()) {
            $blog = new self($blog_obj_id, false);
            if ($blog->hasApproval()) {
                switch ($a_action) {
                    case "update":
                        // un-approved posting was updated - no notifications
                        return;

                    case "new":
                        // un-approved posting was activated - admin-only notification
                        $admin_only = true;
                        $ignore_threshold = true;
                        $a_action = "approve";
                        break;
                }
            }
        }
        
        // create/update news item (only in repository)
        if (!$a_in_wsp &&
            in_array($a_action, array("update", "new"))) {
            $posting->handleNews(($a_action === "update"));
        }
        
        // recipients
        $users = ilNotification::getNotificationsForObject(
            ilNotification::TYPE_BLOG,
            $blog_obj_id,
            $a_posting_id,
            $ignore_threshold
        );
        if (!count($users)) {
            return;
        }

        $ntf = new ilSystemNotification($a_in_wsp);
        $ntf->setLangModules(array("blog"));
        $ntf->setRefId($a_blog_node_id);
        $ntf->setChangedByUserId($ilUser->getId());
        $ntf->setSubjectLangId('blog_change_notification_subject');
        $ntf->setIntroductionLangId('blog_change_notification_body_' . $a_action);
        $ntf->addAdditionalInfo('blog_posting', $posting->getTitle());
        if ($a_comment) {
            $ntf->addAdditionalInfo('comment', $a_comment, true);
        }
        $ntf->setGotoLangId('blog_change_notification_link');
        $ntf->setReasonLangId('blog_change_notification_reason');
        
        $abstract = $posting->getNotificationAbstract();
        if ($abstract) {
            $ntf->addAdditionalInfo('content', $abstract, true);
        }

        $notified = $ntf->sendMailAndReturnRecipients(
            $users,
            "_" . $a_posting_id,
            ($admin_only ? "write" : "read")
        );
                
        // #14387
        if (count($notified)) {
            ilNotification::updateNotificationTime(ilNotification::TYPE_BLOG, $blog_obj_id, $notified, $a_posting_id);
        }
    }
            
    /**
     * Deliver blog as rss feed
     */
    public static function deliverRSS(string $a_wsp_id) : void
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $ilSetting = $DIC->settings();
        
        if (!$ilSetting->get('enable_global_profiles')) {
            return;
        }
        
        // #10827
        if (substr($a_wsp_id, -4) !== "_cll") {
            $wsp_id = new ilWorkspaceTree(0);
            $obj_id = $wsp_id->lookupObjectId((int) $a_wsp_id);
            $is_wsp = "_wsp";
        } else {
            $a_wsp_id = substr($a_wsp_id, 0, -4);
            $obj_id = ilObject::_lookupObjId((int) $a_wsp_id);
            $is_wsp = null;
        }
        if (!$obj_id) {
            return;
        }
        
        $blog = new self($obj_id, false);
        if (!$blog->hasRSS()) {
            return;
        }
                    
        $feed = new ilFeedWriter();
                
        $url = ilLink::_getStaticLink($a_wsp_id, "blog", true, $is_wsp);
        $url = str_replace("&", "&amp;", $url);
        
        // #11870
        $feed->setChannelTitle(str_replace("&", "&amp;", $blog->getTitle()));
        $feed->setChannelDescription(str_replace("&", "&amp;", $blog->getDescription()));
        $feed->setChannelLink($url);
        
        // needed for blogpostinggui / pagegui
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        
        foreach (ilBlogPosting::getAllPostings($obj_id) as $item) {
            $id = $item["id"];

            // only published items
            $is_active = ilBlogPosting::_lookupActive($id, "blp");
            if (!$is_active) {
                continue;
            }
                                    
            // #16434
            $snippet = strip_tags(ilBlogPostingGUI::getSnippet($id), "<br><br/><div><p>");
            $snippet = str_replace("&", "&amp;", $snippet);
            $snippet = "<![CDATA[" . $snippet . "]]>";

            $url = ilLink::_getStaticLink($a_wsp_id, "blog", true, "_" . $id . $is_wsp);
            $url = str_replace("&", "&amp;", $url);

            $feed_item = new ilFeedItem();
            $feed_item->setTitle(str_replace("&", "&amp;", $item["title"])); // #16022
            $feed_item->setDate($item["created"]->get(IL_CAL_DATETIME));
            $feed_item->setDescription($snippet);
            $feed_item->setLink($url);
            $feed_item->setAbout($url);
            $feed->addItem($feed_item);
        }
        
        $feed->showFeed();
        exit();
    }
    
    public function initDefaultRoles() : void
    {
        ilObjRole::createDefaultRole(
            'il_blog_contributor_' . $this->getRefId(),
            "Contributor of blog obj_no." . $this->getId(),
            'il_blog_contributor',
            $this->getRefId()
        );
        
        ilObjRole::createDefaultRole(
            'il_blog_editor_' . $this->getRefId(),
            "Editor of blog obj_no." . $this->getId(),
            'il_blog_editor',
            $this->getRefId()
        );
    }
    
    public function getLocalContributorRole(int $a_node_id) : int
    {
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            if (strpos(ilObject::_lookupTitle($role_id), "il_blog_contributor") === 0) {
                return $role_id;
            }
        }
        return 0;
    }
    
    public function getLocalEditorRole(int $a_node_id) : int
    {
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            if (strpos(ilObject::_lookupTitle($role_id), "il_blog_editor") === 0) {
                return $role_id;
            }
        }
        return 0;
    }
    
    public function getAllLocalRoles(int $a_node_id) : array
    {
        $res = array();
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            $res[$role_id] = ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id));
        }
        
        asort($res);
        return $res;
    }
    
    public function getRolesWithContributeOrRedact(int $a_node_id) : array
    {
        $contr_op_id = ilRbacReview::_getOperationIdByName("contribute");
        $redact_op_id = ilRbacReview::_getOperationIdByName("redact");
        $contr_role_id = $this->getLocalContributorRole($a_node_id);
        $editor_role_id = $this->getLocalEditorRole($a_node_id);
        
        $res = array();
        foreach ($this->rbac_review->getParentRoleIds($a_node_id) as $role_id => $role) {
            if ($role_id != $contr_role_id &&
                $role_id != $editor_role_id) {
                $all_ops = $this->rbac_review->getActiveOperationsOfRole($a_node_id, $role_id);
                if (in_array($contr_op_id, $all_ops) ||
                    in_array($redact_op_id, $all_ops)) {
                    $res[$role_id] = ilObjRole::_getTranslation($role["title"]);
                }
            }
        }
    
        return $res;
    }
    
    protected function handleQuotaUpdate() : void
    {
    }
}
