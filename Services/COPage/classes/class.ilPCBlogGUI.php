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
 * Class ilPCBlogGUI
 * Handles user commands on blog data
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCBlogGUI extends ilPageContentGUI
{
    protected int $requested_blog;
    protected int $requested_blog_id;
    protected ilObjUser $user;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);

        // ... not sure why different ids are used for this...
        $this->requested_blog_id = $this->request->getInt("blog_id");
        $this->requested_blog = $this->request->getInt("blog");
    }

    /**
    * execute command
    */
    public function executeCommand(): string
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

        return (string) $ret;
    }

    public function insert(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    public function edit(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    protected function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_blog"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_blog"));
        }

        $options = array();
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
    public function create(): void
    {
        if ($this->requested_blog_id == 0) {
            $form = $this->initForm(true);
            if ($form->checkInput()) {
                $this->insertPosting($this->requested_blog);
                return;
            }

            $form->setValuesByPost();
            $this->insert($form);
        } else {
            $form = $this->initPostingForm($this->requested_blog_id, true);
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
            $this->insertPosting($this->requested_blog_id, $form);
        }
    }

    /**
     * Update blog
     */
    public function update(): void
    {
        if ($this->requested_blog_id == 0) {
            $form = $this->initForm();
            if ($form->checkInput()) {
                $this->editPosting($this->requested_blog);
                return;
            }

            $this->pg_obj->addHierIDs();
            $form->setValuesByPost();
            $this->edit($form);
        } else {
            $form = $this->initPostingForm($this->requested_blog_id);
            if ($form->checkInput()) {
                $this->content_obj->setData($form->getInput("blog_id"), $form->getInput("posting"));
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }
            }

            $this->pg_obj->addHierIDs();
            $form->setValuesByPost();
            $this->editPosting($this->requested_blog_id, $form);
        }
    }


    /**
     * Insert new blog posting form.
     */
    public function insertPosting(
        int $a_blog_id,
        ilPropertyFormGUI $a_form = null
    ): void {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initPostingForm($a_blog_id, true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit blog posting form
     */
    public function editPosting(
        int $a_blog_id,
        ilPropertyFormGUI $a_form = null
    ): void {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initPostingForm($a_blog_id);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init blog posting form
     */
    protected function initPostingForm(
        int $a_blog_id,
        bool $a_insert = false
    ): ilPropertyFormGUI {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_blog"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_blog"));
        }

        $options = array();
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
