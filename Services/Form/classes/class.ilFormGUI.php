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

/**
 * This class represents a form user interface
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFormGUI
{
    protected string $formaction = "";
    protected bool $multipart = false;
    protected bool $keepopen = false;
    protected bool $opentag = true;
    protected string $id = '';
    protected string $name = '';
    protected string $target = '';
    protected bool $prevent_double_submission = false;

    public function setFormAction(string $a_formaction) : void
    {
        $this->formaction = $a_formaction;
    }

    public function getFormAction() : string
    {
        return $this->formaction;
    }

    public function setTarget(string $a_target) : void
    {
        $this->target = $a_target;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function setMultipart(bool $a_multipart) : void
    {
        $this->multipart = $a_multipart;
    }

    public function getMultipart() : bool
    {
        return $this->multipart;
    }

    public function setId(string $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : string
    {
        return $this->id;
    }
    
    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setKeepOpen(bool $a_keepopen) : void
    {
        $this->keepopen = $a_keepopen;
    }

    public function getKeepOpen() : bool
    {
        return $this->keepopen;
    }

    public function setOpenTag(bool $a_open) : void
    {
        $this->opentag = $a_open;
    }

    public function getOpenTag() : bool
    {
        return $this->opentag;
    }
    
    public function setCloseTag(bool $a_val) : void
    {
        $this->setKeepOpen(!$a_val);
    }
    
    public function getCloseTag() : bool
    {
        return !$this->getKeepOpen();
    }
    
    public function setPreventDoubleSubmission(bool $a_val) : void
    {
        $this->prevent_double_submission = $a_val;
    }
    
    public function getPreventDoubleSubmission() : bool
    {
        return $this->prevent_double_submission;
    }
    
    public function getHTML() : string
    {
        $tpl = new ilTemplate("tpl.form.html", true, true, "Services/Form");
        
        // this line also sets multipart, so it must be before the multipart check
        $content = $this->getContent();
        if ($this->getOpenTag()) {
            $opentpl = new ilTemplate('tpl.form_open.html', true, true, "Services/Form");
            if ($this->getTarget() != "") {
                $opentpl->setCurrentBlock("form_target");
                $opentpl->setVariable("FORM_TARGET", $this->getTarget());
                $opentpl->parseCurrentBlock();
            }
            if ($this->getName() != "") {
                $opentpl->setCurrentBlock("form_name");
                $opentpl->setVariable("FORM_NAME", $this->getName());
                $opentpl->parseCurrentBlock();
            }
            if ($this->getPreventDoubleSubmission()) {
                $opentpl->setVariable("FORM_CLASS", "preventDoubleSubmission");
            }

            if ($this->getMultipart()) {
                $opentpl->touchBlock("multipart");
                /*if (function_exists("apc_fetch"))
                //
                // Progress bar would need additional browser window (popup)
                // to not be stopped, when form is submitted  (we can't work
                // with an iframe or httprequest solution here)
                //
                {
                    $tpl->touchBlock("onsubmit");

                    //onsubmit="postForm('{ON_ACT}','form_{F_ID}',1); return false;"
                    $tpl->setCurrentBlock("onsubmit");
                    $tpl->setVariable("ON_ACT", $this->getFormAction());
                    $tpl->setVariable("F_ID", $this->getId());
                    $tpl->setVariable("F_ID", $this->getId());
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock("hidden_progress");
                    $tpl->setVariable("APC_PROGRESS_ID", uniqid());
                    $tpl->setVariable("APC_FORM_ID", $this->getId());
                    $tpl->parseCurrentBlock();
                }*/
            }
            $opentpl->setVariable("FORM_ACTION", $this->getFormAction());
            if ($this->getId() != "") {
                $opentpl->setVariable("FORM_ID", $this->getId());
            }
            $opentpl->parseCurrentBlock();
            $tpl->setVariable('FORM_OPEN_TAG', $opentpl->get());
        }
        $tpl->setVariable("FORM_CONTENT", $content);
        if (!$this->getKeepOpen()) {
            $tpl->setVariable("FORM_CLOSE_TAG", "</form>");
        }
        return $tpl->get();
    }

    public function getContent() : string
    {
        return "";
    }
}
