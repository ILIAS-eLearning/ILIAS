<?php

declare(strict_types=1);

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
 * This class represents an image file property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilImageFileInputGUI extends ilFileInputGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected bool $cache = false;
    protected string $alt = "";
    protected string $image = "";
    protected bool $allow_capture = false;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);
        $this->setType("image_file");
        $this->setALlowDeletion(true);
        $this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
        $this->setHiddenTitle("(" . $lng->txt("form_image_file_input") . ")");
        $this->cache = true;
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public function setAllowDeletion(bool $a_val): void
    {
        $this->allow_deletion = $a_val;
    }

    public function getALlowDeletion(): bool
    {
        return $this->allow_deletion;
    }

    public function setAllowCapture(bool $a_val): void
    {
        $this->allow_capture = $a_val;
    }

    public function getAllowCapture(): bool
    {
        return $this->allow_capture;
    }

    /**
     * Set cache
     *
     * @param bool $a_cache If false, the image will be forced to reload in the browser
     * by adding an URL parameter with the actual timestamp
     */
    public function setUseCache(bool $a_cache): void
    {
        $this->cache = $a_cache;
    }

    public function getUseCache(): bool
    {
        return $this->cache;
    }

    public function setImage(string $a_image): void
    {
        $this->image = $a_image;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setAlt(string $a_alt): void
    {
        $this->alt = $a_alt;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $quota_exceeded = $quota_legend = false;
        $i_tpl = new ilTemplate("tpl.prop_image_file.html", true, true, "Services/Form");

        if ($this->getAllowCapture()) {
            $i_tpl->setCurrentBlock("capture");
            $i_tpl->setVariable("POST_VAR_V", $this->getPostVar());
            $i_tpl->setVariable("TXT_USE_CAMERA", $lng->txt("form_use_camera"));
            $i_tpl->setVariable("TXT_TAKE_SNAPSHOT", $lng->txt("form_take_snapshot"));
            $i_tpl->parseCurrentBlock();
            $main_tpl = $this->tpl;
            $main_tpl->addJavascript("./Services/Form/js/ServiceFormImageFileCapture.js");
        }

        if ($this->getImage() != "") {
            if (!$this->getDisabled() && $this->getALlowDeletion()) {
                $i_tpl->setCurrentBlock("delete_bl");
                $i_tpl->setVariable("POST_VAR_D", $this->getPostVar());
                $i_tpl->setVariable(
                    "TXT_DELETE_EXISTING",
                    $lng->txt("delete_existing_file")
                );
                $i_tpl->parseCurrentBlock();
            }

            if (strlen($this->getValue())) {
                $i_tpl->setCurrentBlock("has_value");
                $i_tpl->setVariable("TEXT_IMAGE_NAME", $this->getValue());
                $i_tpl->parseCurrentBlock();
            }
            $i_tpl->setCurrentBlock("image");
            if (!$this->getUseCache()) {
                $pos = strpos($this->getImage(), '?');
                if ($pos !== false) {
                    $i_tpl->setVariable("SRC_IMAGE", $this->getImage() . "&amp;time=" . time());
                } else {
                    $i_tpl->setVariable("SRC_IMAGE", $this->getImage() . "?time=" . time());
                }
            } else {
                $i_tpl->setVariable("SRC_IMAGE", $this->getImage());
            }
            $i_tpl->setVariable("POST_VAR_I", $this->getPostVar());
            $i_tpl->setVariable("ALT_IMAGE", $this->getAlt());
            $i_tpl->parseCurrentBlock();
        }

        $pending = $this->getPending();
        if ($pending) {
            $i_tpl->setCurrentBlock("pending");
            $i_tpl->setVariable("TXT_PENDING", $lng->txt("file_upload_pending") .
                ": " . htmlentities($pending));
            $i_tpl->parseCurrentBlock();
        }

        $i_tpl->setVariable("POST_VAR", $this->getPostVar());
        $i_tpl->setVariable("ID", $this->getFieldId());


        /* experimental: bootstrap'ed file upload */
        $i_tpl->setVariable("TXT_BROWSE", $lng->txt("select_file"));


        if (!$quota_exceeded) {
            $i_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " .
                $this->getMaxFileSizeString() . $quota_legend);

            $this->outputSuffixes($i_tpl, "allowed_image_suffixes");
        } else {
            $i_tpl->setVariable("TXT_MAX_SIZE", $quota_exceeded);
        }

        if ($this->getDisabled() || $quota_exceeded) {
            $i_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $i_tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    public function getDeletionFlag(): bool
    {
        if ($this->str($this->getPostVar() . "_delete") != "") {
            return true;
        }
        return false;
    }
}
