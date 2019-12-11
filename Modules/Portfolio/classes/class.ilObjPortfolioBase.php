<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
 * Portfolio base
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
abstract class ilObjPortfolioBase extends ilObject2
{

    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;
        parent::__construct($a_id, $a_reference);

        $this->db = $DIC->database();
    }

    protected $online; // [bool]
    protected $comments; // [bool]
    protected $bg_color; // [string]
    protected $font_color; // [string]
    protected $img; // [string]
    protected $ppic; // [string]
    protected $style; // [bool]
    
    
    //
    // PROPERTIES
    //

    /**
     * Set online status
     *
     * @param bool $a_value
     */
    public function setOnline($a_value)
    {
        $this->online = (bool) $a_value;
    }

    /**
     * Is online?
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }
    
    /**
     * Is online?
     *
     * @return bool
     */
    public static function lookupOnline($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT is_online" .
            " FROM usr_portfolio" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return  (bool) $row["is_online"];
    }
    
    /**
     * Set public comments status
     *
     * @param bool $a_value
     */
    public function setPublicComments($a_value)
    {
        $this->comments = (bool) $a_value;
    }

    /**
     * Active public comments?
     *
     * @return bool
     */
    public function hasPublicComments()
    {
        return $this->comments;
    }
    
    /**
     * Get profile picture status
     *
     * @return bool
     */
    public function hasProfilePicture()
    {
        return $this->ppic;
    }

    /**
     * Toggle profile picture status
     *
     * @param bool $a_status
     */
    public function setProfilePicture($a_status)
    {
        $this->ppic = (bool) $a_status;
    }

    /**
     * Get background color
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        if (!$this->bg_color) {
            $this->bg_color = "ffffff";
        }
        return $this->bg_color;
    }

    /**
     * Set background color
     *
     * @param string $a_value
     */
    public function setBackgroundColor($a_value)
    {
        $this->bg_color = (string) $a_value;
    }
    
    /**
     * Get font color
     *
     * @return string
     */
    public function getFontColor()
    {
        if (!$this->font_color) {
            $this->font_color = "505050";
        }
        return $this->font_color;
    }

    /**
     * Set font color
     *
     * @param string $a_value
     */
    public function setFontColor($a_value)
    {
        $this->font_color = (string) $a_value;
    }
    
    /**
     * Get banner image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->img;
    }

    /**
     * Set banner image
     *
     * @param string $a_value
     */
    public function setImage($a_value)
    {
        $this->img = (string) $a_value;
    }
    
    /**
     * Get style sheet id
     *
     * @return bool
     */
    public function getStyleSheetId()
    {
        return (int) $this->style;
    }

    /**
     * Set style sheet id
     *
     * @param int $a_style
     */
    public function setStyleSheetId($a_style)
    {
        $this->style = (int) $a_style;
    }
    
    
    //
    // CRUD
    //
    
    protected function doRead()
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM usr_portfolio" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        
        $this->setOnline((bool) $row["is_online"]);
        $this->setProfilePicture((bool) $row["ppic"]);
        $this->setBackgroundColor($row["bg_color"]);
        $this->setFontColor($row["font_color"]);
        $this->setImage($row["img"]);
        
        // #14661
        include_once("./Services/Notes/classes/class.ilNote.php");
        $this->setPublicComments(ilNote::commentsActivated($this->id, 0, $this->getType()));
        
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->setStyleSheetId(ilObjStyleSheet::lookupObjectStyle($this->id));
        
        $this->doReadCustom($row);
    }
    
    protected function doReadCustom(array $a_row)
    {
    }

    protected function doCreate()
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate("INSERT INTO usr_portfolio (id,is_online)" .
            " VALUES (" . $ilDB->quote($this->id, "integer") . "," .
            $ilDB->quote(0, "integer") . ")");
    }
    
    protected function doUpdate()
    {
        $ilDB = $this->db;
        
        $fields = array(
            "is_online" => array("integer", $this->isOnline()),
            "ppic" => array("integer", $this->hasProfilePicture()),
            "bg_color" => array("text", $this->getBackgroundColor()),
            "font_color" => array("text", $this->getFontcolor()),
            "img" => array("text", $this->getImage())
        );
        $this->doUpdateCustom($fields);
        
        // #14661
        include_once("./Services/Notes/classes/class.ilNote.php");
        ilNote::activateComments($this->id, 0, $this->getType(), $this->hasPublicComments());
        
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        ilObjStyleSheet::writeStyleUsage($this->id, $this->getStyleSheetId());
                
        $ilDB->update(
            "usr_portfolio",
            $fields,
            array("id"=>array("integer", $this->id))
        );
    }
    
    protected function doUpdateCustom(array &$a_fields)
    {
    }

    protected function doDelete()
    {
        $ilDB = $this->db;
        
        $this->deleteAllPages();
        $this->deleteImage();

        $ilDB->manipulate("DELETE FROM usr_portfolio" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }
    
    abstract protected function deleteAllPages();
    
    
    //
    // IMAGES
    //
    
    /**
     * Get banner image incl. path
     *
     * @param bool $a_as_thumb
     */
    public function getImageFullPath($a_as_thumb = false)
    {
        if ($this->img) {
            $path = $this->initStorage($this->id);
            if (!$a_as_thumb) {
                return $path . $this->img;
            } else {
                return $path . "thb_" . $this->img;
            }
        }
    }
    
    /**
     * remove existing file
     */
    public function deleteImage()
    {
        if ($this->id) {
            include_once "Modules/Portfolio/classes/class.ilFSStoragePortfolio.php";
            $storage = new ilFSStoragePortfolio($this->id);
            $storage->delete();
            
            $this->setImage(null);
        }
    }
        
    /**
     * Init file system storage
     *
     * @param type $a_id
     * @param type $a_subdir
     * @return string
     */
    public static function initStorage($a_id, $a_subdir = null)
    {
        include_once "Modules/Portfolio/classes/class.ilFSStoragePortfolio.php";
        $storage = new ilFSStoragePortfolio($a_id);
        $storage->create();
        
        $path = $storage->getAbsolutePath() . "/";
        
        if ($a_subdir) {
            $path .= $a_subdir . "/";
            
            if (!is_dir($path)) {
                mkdir($path);
            }
        }
                
        return $path;
    }
    
    /**
     * Upload new image file
     *
     * @param array $a_upload
     * @return bool
     */
    public function uploadImage(array $a_upload)
    {
        if (!$this->id) {
            return false;
        }
        
        $this->deleteImage();
        
        // #10074
        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $a_upload["name"]);
    
        $path = $this->initStorage($this->id);
        $original = "org_" . $this->id . "_" . $clean_name;
        $thumb = "thb_" . $this->id . "_" . $clean_name;
        $processed = $this->id . "_" . $clean_name;
        
        if (ilUtil::moveUploadedFile($a_upload["tmp_name"], $original, $path . $original)) {
            chmod($path . $original, 0770);
            
            $prfa_set = new ilSetting("prfa");
            /* as banner height should overflow, we only handle width
            $dimensions = $prfa_set->get("banner_width")."x".
                $prfa_set->get("banner_height");
            */
            $dimensions = $prfa_set->get("banner_width");

            // take quality 100 to avoid jpeg artefacts when uploading jpeg files
            // taking only frame [0] to avoid problems with animated gifs
            $original_file = ilUtil::escapeShellArg($path . $original);
            $thumb_file = ilUtil::escapeShellArg($path . $thumb);
            $processed_file = ilUtil::escapeShellArg($path . $processed);
            ilUtil::execConvert($original_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
            ilUtil::execConvert($original_file . "[0] -geometry " . $dimensions . " -quality 100 JPEG:" . $processed_file);
            
            $this->setImage($processed);
            
            return true;
        }
        return false;
    }
    
    
    //
    // TRANSMOGRIFIER
    //
        
    /**
     * Clone basic settings
     *
     * @param ilObjPortfolioBase $a_source
     * @param ilObjPortfolioBase $a_target
     */
    protected static function cloneBasics(ilObjPortfolioBase $a_source, ilObjPortfolioBase $a_target)
    {
        // copy portfolio properties
        $a_target->setPublicComments($a_source->hasPublicComments());
        $a_target->setProfilePicture($a_source->hasProfilePicture());
        $a_target->setFontColor($a_source->getFontColor());
        $a_target->setBackgroundColor($a_source->getBackgroundColor());
        $a_target->setImage($a_source->getImage());
        $a_target->update();

        // banner/images
        $source_dir = $a_source->initStorage($a_source->getId());
        $target_dir = $a_target->initStorage($a_target->getId());
        ilFSStoragePortfolio::_copyDirectory($source_dir, $target_dir);
        
        // set/copy stylesheet
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = $a_source->getStyleSheetId();
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            $a_target->setStyleSheetId($new_id);
            $a_target->update();
        }
    }

    /**
     * Build template from portfolio and vice versa
     *
     * @param ilObjPortfolioBase $a_source
     * @param ilObjPortfolioBase $a_target
     * @param array $a_recipe
     */
    public static function clonePagesAndSettings(ilObjPortfolioBase $a_source, ilObjPortfolioBase $a_target, array $a_recipe = null, $copy_all = false)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilUser = $DIC->user();
        
        $source_id = $a_source->getId();
        $target_id = $a_target->getId();
        
        if ($a_source instanceof ilObjPortfolioTemplate &&
            $a_target instanceof ilObjPortfolio) {
            $direction = "t2p";
        } elseif ($a_source instanceof ilObjPortfolio &&
            $a_target instanceof ilObjPortfolioTemplate) {
            $direction = "p2t";
        } else {
            return;
        }
        
        self::cloneBasics($a_source, $a_target);
        
        // personal skills
        include_once "Services/Skill/classes/class.ilPersonalSkill.php";
        $pskills = array_keys(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));
        
        // copy pages
        $blog_count = 0;
        include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
        $page_map = array();
        foreach (ilPortfolioPage::getAllPortfolioPages($source_id) as $page) {
            $page_id = $page["id"];
            
            if ($direction == "t2p") {
                $source_page = new ilPortfolioTemplatePage($page_id);
                $target_page = new ilPortfolioPage();
            } else {
                $source_page = new ilPortfolioPage($page_id);
                $target_page = new ilPortfolioTemplatePage();
            }
            $source_page->setPortfolioId($source_id);
            $target_page->setPortfolioId($target_id);
            
            $page_type = $source_page->getType();
            $page_title = $source_page->getTitle();

            
                

            $page_recipe = null;
            if (is_array($a_recipe)) {
                $page_recipe = $a_recipe[$page_id];
            }

            $valid = false;
            switch ($page_type) {
                // blog => blog template
                case ilPortfolioTemplatePage::TYPE_BLOG:
                    if ($direction == "p2t") {
                        $page_type = ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE;
                        $page_title = $lng->txt("obj_blog") . " " . (++$blog_count);
                        $valid = true;
                    }
                    break;
                
                // blog template => blog (needs recipe)
                case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
                    if ($direction == "t2p" && (is_array($page_recipe) || $copy_all)) {
                        $page_type = ilPortfolioPage::TYPE_BLOG;
                        if ($copy_all) {
                            $page_title = self::createBlogInPersonalWorkspace($page_title);
                            $valid = true;
                        } else {
                            if ($page_recipe[0] == "blog") {
                                switch ($page_recipe[1]) {
                                    case "create":
                                        $page_title = self::createBlogInPersonalWorkspace($page_recipe[2]);
                                        $valid = true;
                                        break;

                                    case "reuse":
                                        $page_title = $page_recipe[2];
                                        $valid = true;
                                        break;

                                    case "ignore":
                                        // do nothing
                                        break;
                                }
                            }
                        }
                    }
                    break;

                // page editor
                default:
                    $target_page->setXMLContent($source_page->copyXmlContent(true)); // copy mobs
                    $target_page->buildDom(true);


                    // parse content / blocks

                    if ($direction == "t2p") {
                        $dom = $target_page->getDom();
                        if ($dom instanceof php4DOMDocument) {
                            $dom = $dom->myDOMDocument;
                        }
                        
                        // update profile/consultation hours user id
                        self::updateDomNodes($dom, "//PageContent/Profile", "User", $ilUser->getId());
                        self::updateDomNodes($dom, "//PageContent/ConsultationHours", "User", $ilUser->getId());
                        self::updateDomNodes($dom, "//PageContent/MyCourses", "User", $ilUser->getId());
                    
                        // skills
                        $xpath = new DOMXPath($dom);
                        $nodes = $xpath->query("//PageContent/Skills");
                        foreach ($nodes as $node) {
                            $skill_id = $node->getAttribute("Id");

                            // existing personal skills
                            if (in_array($skill_id, $pskills)) {
                                $node->setAttribute("User", $ilUser->getId());
                            }
                            // new skill
                            elseif ($copy_all || in_array($skill_id, $a_recipe["skills"])) {
                                include_once "Services/Skill/classes/class.ilPersonalSkill.php";
                                ilPersonalSkill::addPersonalSkill($ilUser->getId(), $skill_id);

                                $node->setAttribute("User", $ilUser->getId());
                            }
                            // remove skill
                            else {
                                $page_element = $node->parentNode;
                                $page_element->parentNode->removeChild($page_element);
                            }
                        }
                    }

                    $valid = true;
                    break;
            }

            if ($valid) {
                // #12038 - update xml from dom
                $target_page->setXMLContent($target_page->getXMLFromDom());
                
                $target_page->setType($page_type);
                $target_page->setTitle($page_title);
                $target_page->create();

                if ($page_type == ilPortfolioPage::TYPE_PAGE) {
                    $target_page->update();	// handle mob usages!
                }
                $page_map[$source_page->getId()] = $target_page->getId();
            }
        }

        ilPortfolioPage::updateInternalLinks($page_map, $a_target);
    }
        
    protected static function updateDomNodes($a_dom, $a_xpath, $a_attr_id, $a_attr_value)
    {
        $xpath_temp = new DOMXPath($a_dom);
        $nodes = $xpath_temp->query($a_xpath);
        foreach ($nodes as $node) {
            $node->setAttribute($a_attr_id, $a_attr_value);
        }
    }
    
    protected static function createBlogInPersonalWorkspace($a_title)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        static $ws_access = null;
        
        include_once "Modules/Blog/classes/class.ilObjBlog.php";
        $blog = new ilObjBlog();
        $blog->setType("blog");
        $blog->setTitle($a_title);
        $blog->create();
        
        if (!$ws_access) {
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
            $tree = new ilWorkspaceTree($ilUser->getId());
            
            // #13235
            if (!$tree->getRootId()) {
                $tree->createTreeForUser($ilUser->getId());
            }
            
            $ws_access = new ilWorkspaceAccessHandler($tree);
        }
        
        $tree = $ws_access->getTree();
        $node_id = $tree->insertObject($tree->getRootId(), $blog->getId());
        $ws_access->setPermissions($tree->getRootId(), $node_id);
        
        return $blog->getId();
    }

    /**
     * Fix internal portfolio links
     *
     * @param array
     */
    public function fixLinksOnTitleChange($a_title_changes)
    {
        foreach (ilPortfolioPage::getAllPortfolioPages($this->getId()) as $port_page) {
            if ($this->getType() == "prtt") {
                $page = new ilPortfolioTemplatePage($port_page["id"]);
            } else {
                $page = new ilPortfolioPage($port_page["id"]);
            }
            if ($page->renameLinksOnTitleChange($a_title_changes)) {
                $page->update(true, true);
            }
        }
    }
}
