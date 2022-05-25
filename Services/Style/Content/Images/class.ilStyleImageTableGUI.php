<?php declare(strict_types=1);

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

use ILIAS\Style\Content\Access;
use ILIAS\Style\Content;
use ILIAS\DI\UIServices;

/**
 * TableGUI class for style editor (image list)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleImageTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjStyleSheet $style_obj;
    protected Access\StyleAccessManager $access_manager;
    protected UIServices $ui;
    protected Content\ImageManager $image_manager;

    /**
    * Constructor
    */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        Access\StyleAccessManager $access_manager,
        Content\ImageManager $image_manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->access_manager = $access_manager;
        $this->image_manager = $image_manager;
        $this->ui = $DIC->ui();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("sty_images"));

        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("thumbnail"));
        $this->addColumn($this->lng->txt("file"), "file");
        $this->addColumn($this->lng->txt("sty_width_height"));
        $this->addColumn($this->lng->txt("size"));
        $this->addColumn($this->lng->txt("actions"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_image_row.html", "Services/Style/Content/Images");
        $this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->access_manager->checkWrite()) {
            $this->addMultiCommand("deleteImage", $lng->txt("delete"));
        }
        
        //$this->addMultiCommand("editLink", $lng->txt("cont_set_link"));
        //$this->addCommandButton("addImage", $this->lng->txt("sty_add_image"));
        
        $this->setEnableTitle(true);
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     */
    public function getItems() : void
    {
        $images = [];
        /** @var Content\Image $i */
        foreach ($this->image_manager->getImages() as $i) {
            $images[] = [
                "file" => $i->getFilename(),
                "obj" => $i
            ];
        }
        $this->setData($images);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;

        /** @var Content\Image $image */
        $image = $a_set["obj"];

        $image_file = $this->image_manager->getWebPath($image);
        if (is_file($image_file)) {
            $this->tpl->setCurrentBlock("thumbnail");
            $this->tpl->setVariable("IMG_ALT", $image->getFilename());
            $this->tpl->setVariable("IMG_SRC", $image_file);
            $this->tpl->parseCurrentBlock();
        }

        if ($image->getWidth() > 0 && $image->getHeight() > 0) {
            $this->tpl->setVariable(
                "VAL_WIDTH_HEIGHT",
                $image->getWidth() . "px x " . $image->getHeight() . "px"
            );
        }

        $size = $image->getSize();
        $this->tpl->setVariable("VAL_FILENAME", $image->getFilename());
        $this->tpl->setVariable(
            "VAL_SIZE",
            round($size->getSize(), 1) . " " . "kB"
        );
        $this->tpl->setVariable("FILE", $image->getFilename());

        if ($this->access_manager->checkWrite()) {
            $ilCtrl->setParameter($this->parent_obj, "file", rawurlencode($image->getFilename()));

            $links = [];
            if ($this->image_manager->supportsResize($image)) {
                $links[] = $ui->factory()->link()->standard(
                    $this->lng->txt("sty_resize"),
                    $ilCtrl->getLinkTargetByClass("ilContentStyleImageGUI", "resizeImageForm")
                );
            }

            $dd = $ui->factory()->dropdown()->standard($links);

            $this->tpl->setVariable(
                "ACTIONS",
                $ui->renderer()->render($dd)
            );
        }
    }
}
