<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCBlog.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCBlogGUI
*
* Handles user commands on blog data
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCBlogGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;


    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert blog form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function insert(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit blog form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init blog form
     *
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     */
    protected function initForm($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_blog"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_blog"));
        }
                
        $options = array();
        include_once "Modules/Blog/classes/class.ilBlogPosting.php";
        $blogs_ids = ilBlogPosting::searchBlogsByAuthor($ilUser->getId());
        if ($blogs_ids) {
            foreach ($blogs_ids as $blog_id) {
                $options[$blog_id] = ilObject::_lookupTitle($blog_id);
            }
            asort($options);
        }
        $obj = new ilSelectInputGUI($this->lng->txt("cont_pc_blog"), "blog");
        $obj->setRequired(true);
        $obj->setOptions($options);
        $form->addItem($obj);
        
        if ($a_insert) {
            $form->addCommandButton("create_blog", $this->lng->txt("select"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $obj->setValue($this->content_obj->getBlogId());
            $form->addCommandButton("update", $this->lng->txt("select"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    /**
    * Create new blog
    */
    public function create()
    {
        if (!$_POST["blog_id"]) {
            $form = $this->initForm(true);
            if ($form->checkInput()) {
                return $this->insertPosting($_POST["blog"]);
            }
            
            $form->setValuesByPost();
            return $this->insert($form);
        } else {
            $form = $this->initPostingForm($_POST["blog_id"], true);
            if ($form->checkInput()) {
                $this->content_obj = new ilPCBlog($this->getPage());
                $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
                $this->content_obj->setData($form->getInput("blog_id"), $form->getInput("posting"));
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }
            }
            
            $form->setValuesByPost();
            return $this->insertPosting($_POST["blog_id"], $form);
        }
    }

    /**
    * Update blog
    */
    public function update()
    {
        if (!$_POST["blog_id"]) {
            $form = $this->initForm();
            if ($form->checkInput()) {
                return $this->editPosting($_POST["blog"]);
            }
            
            $this->pg_obj->addHierIDs();
            $form->setValuesByPost();
            return $this->edit($form);
        } else {
            $form = $this->initPostingForm($_POST["blog_id"]);
            if ($form->checkInput()) {
                $this->content_obj->setData($form->getInput("blog_id"), $form->getInput("posting"));
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }
            }
            
            $this->pg_obj->addHierIDs();
            $form->setValuesByPost();
            return $this->editPosting($_POST["blog_id"], $form);
        }
    }
    
    
    /**
     * Insert new blog posting form.
     *
     * @param int $a_blog_id
     * @param ilPropertyFormGUI $a_form
     */
    public function insertPosting($a_blog_id, ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initPostingForm($a_blog_id, true);
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit blog posting form
     *
     * @param int $a_blog_id
     * @param ilPropertyFormGUI $a_form
     */
    public function editPosting($a_blog_id, ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initPostingForm($a_blog_id);
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init blog posting form
     *
     * @param int $a_blog_id
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     */
    protected function initPostingForm($a_blog_id, $a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_blog"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_blog"));
        }

        $options = array();
        include_once "Modules/Blog/classes/class.ilBlogPosting.php";
        $postings = ilBlogPosting::getAllPostings($a_blog_id);
        if ($postings) {
            foreach ($postings as $post) {
                // could be posting from someone else
                if ($post["author"] == $ilUser->getId()) {
                    $date = new ilDateTime($post["date"], IL_CAL_DATETIME);
                    $title = $post["title"] . " - " .
                        ilDatePresentation::formatDate($date);

                    $cbox = new ilCheckboxInputGUI($title, "posting");
                    $cbox->setValue($post["id"]);

                    $options[] = $cbox;
                }
            }
        }
        asort($options);
        $obj = new ilCheckboxGroupInputGUI($this->lng->txt("cont_pc_blog_posting"), "posting");
        $obj->setRequired(true);
        $obj->setOptions($options);
        $form->addItem($obj);
        
        $blog_id = new ilHiddenInputGUI("blog_id");
        $blog_id->setValue($a_blog_id);
        $form->addItem($blog_id);
        
        if ($a_insert) {
            $form->addCommandButton("create_blog", $this->lng->txt("save"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $obj->setValue($this->content_obj->getPostings());
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }
}
