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

use Psr\Http\Message\RequestInterface;
use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * This class represents a property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFormPropertyGUI
{
    protected ?ilTable2GUI $parent_table = null;
    protected ?ilFormPropertyGUI $parent_gui = null;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected string $type = "";
    protected string $title = "";
    protected string $postvar = "";
    protected string $info = "";
    protected string $alert = "";
    protected bool $required = false;
    protected ?ilPropertyFormGUI $parentform = null;
    protected string $hidden_title = "";
    protected bool $multi = false;
    protected bool $multi_sortable = false;
    protected bool $multi_addremove = true;
    protected array $multi_values = [];
    protected RequestInterface $request;
    protected HTTP\Services $http;
    protected ?Refinery\Factory $refinery = null;
    protected bool $disabled = false;

    protected ?ilGlobalTemplateInterface $global_tpl = null;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        if (isset($DIC["http"])) {
            $this->http = $DIC->http();
        }

        if (isset($DIC["refinery"])) {
            $this->refinery = $DIC->refinery();
        }

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setTitle($a_title);
        $this->setPostVar($a_postvar);
        $this->setDisabled(false);
        if (isset($DIC["http"])) {      // some unit tests will fail otherwise
            $this->request = $DIC->http()->request();
        }
        if (isset($DIC["tpl"])) {      // some unit tests will fail otherwise
            $this->global_tpl = $DIC['tpl'];
        }
    }

    /**
     * @return mixed
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        return $this->$cmd();
    }

    protected function setType(string $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setPostVar(string $a_postvar): void
    {
        $this->postvar = $a_postvar;
    }

    public function getPostVar(): string
    {
        return $this->postvar;
    }

    public function getFieldId(): string
    {
        $id = str_replace("[", "__", $this->getPostVar());
        $id = str_replace("]", "__", $id);
        return $id;
    }

    public function setInfo(string $a_info): void
    {
        $this->info = $a_info;
    }

    public function getInfo(): string
    {
        return $this->info;
    }

    public function setAlert(string $a_alert): void
    {
        $this->alert = $a_alert;
    }

    public function getAlert(): string
    {
        return $this->alert;
    }

    public function setRequired(bool $a_required): void
    {
        $this->required = $a_required;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setDisabled(bool $a_disabled): void
    {
        $this->disabled = $a_disabled;
    }

    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     */
    public function checkInput(): bool
    {
        return false;		// please overwrite
    }

    public function setParentForm(ilPropertyFormGUI $a_parentform): void
    {
        $this->parentform = $a_parentform;
    }

    public function getParentForm(): ?ilPropertyFormGUI
    {
        return $this->parentform;
    }

    // Set Parent GUI object.
    public function setParent(ilFormPropertyGUI $a_val): void
    {
        $this->parent_gui = $a_val;
    }

    public function getParent(): ?ilFormPropertyGUI
    {
        return $this->parent_gui;
    }

    public function getSubForm(): ?ilPropertyFormGUI
    {
        return null;
    }

    public function hideSubForm(): bool
    {
        return false;
    }

    // Set hidden title (for screenreaders)
    public function setHiddenTitle(string $a_val): void
    {
        $this->hidden_title = $a_val;
    }

    public function getHiddenTitle(): string
    {
        return $this->hidden_title;
    }

    /**
     * Get item by post var
     */
    public function getItemByPostVar(string $a_post_var): ?ilFormPropertyGUI
    {
        if ($this->getPostVar() == $a_post_var) {
            return $this;
        }
        return null;
    }

    public function serializeData(): string
    {
        return serialize($this->getValue());
    }

    public function unserializeData(string $a_data): void
    {
        $data = unserialize($a_data);

        if ($data) {
            $this->setValue($data);
        } else {
            $this->setValue("");
        }
    }

    /**
     * Set parent table
     * @param ilTable2GUI $a_val table object
     */
    public function setParentTable($a_val): void
    {
        $this->parent_table = $a_val;
    }

    /**
     * Get parent table
     * @return ilTable2GUI table object
     */
    public function getParentTable(): ?ilTable2GUI
    {
        return $this->parent_table;
    }

    protected function checkParentFormTable(): void
    {
        $parent = $this->getParentForm();
        $parent_table = $this->getParentTable();
        if (!is_object($parent) && !isset($parent_table)) {
            throw new Exception("Parent form/table not set for " . get_class($this) . " to use serialize feature.");
        }
    }

    /**
     * @throws Exception
     */
    public function writeToSession(): void
    {
        $this->checkParentFormTable();
        ilSession::set($this->getSessionKey(), $this->serializeData());
    }

    protected function getSessionKey(): string
    {
        $parent = $this->getParentForm();
        if (!is_object($parent)) {
            $parent = $this->getParentTable();
        }
        return "form_" . $parent->getId() . "_" . $this->getFieldId();
    }

    /**
     * @throws Exception
     */
    public function clearFromSession(): void
    {
        $this->checkParentFormTable();
        ilSession::clear($this->getSessionKey());
    }

    /**
     * @throws Exception
     */
    public function readFromSession(): void
    {
        $this->checkParentFormTable();
        if (ilSession::has($this->getSessionKey())) {
            $this->unserializeData(ilSession::get($this->getSessionKey()));
        } else {
            $this->unserializeData("");
        }
    }

    public function getHiddenTag(
        string $a_post_var,
        string $a_value
    ): string {
        return '<input type="hidden" name="' . $a_post_var . '" value="' . ilLegacyFormElementsUtil::prepareFormOutput(
            $a_value
        ) . '" />';
    }

    public function setMulti(
        bool $a_multi,
        bool $a_sortable = false,
        bool $a_addremove = true
    ): void {
        if (!$this instanceof ilMultiValuesItem) {
            throw new ilFormException(sprintf(
                "%s not supported for form property type %s",
                __FUNCTION__,
                get_class($this)
            ));
        }

        $this->multi = $a_multi;
        $this->multi_sortable = $a_sortable;
        $this->multi_addremove = $a_addremove;
    }

    public function getMulti(): bool
    {
        return $this->multi;
    }

    public function setMultiValues(array $a_values): void
    {
        $this->multi_values = array_unique($a_values);
    }

    public function getMultiValues(): array
    {
        return $this->multi_values;
    }

    // Get HTML for multiple value icons
    protected function getMultiIconsHTML(): string
    {
        $lng = $this->lng;

        $id = $this->getFieldId();

        $tpl = new ilTemplate("tpl.multi_icons.html", true, true, "Services/Form");

        $html = "";
        if ($this->multi_addremove) {
            $tpl->setCurrentBlock("addremove");
            $tpl->setVariable("ID", $id);
            $tpl->setVariable("TXT_ADD", $lng->txt("add"));
            $tpl->setVariable("TXT_REMOVE", $lng->txt("remove"));
            $tpl->setVariable("SRC_ADD", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("SRC_REMOVE", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
        }

        if ($this->multi_sortable) {
            $tpl->setCurrentBlock("sortable");
            $tpl->setVariable("ID", $id);
            $tpl->setVariable("TXT_DOWN", $lng->txt("down"));
            $tpl->setVariable("TXT_UP", $lng->txt("up"));
            $tpl->setVariable("SRC_UP", ilGlyphGUI::get(ilGlyphGUI::UP));
            $tpl->setVariable("SRC_DOWN", ilGlyphGUI::get(ilGlyphGUI::DOWN));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Get content that has to reside outside of the parent form tag, e.g. panels/layers
     */
    public function getContentOutsideFormTag(): string
    {
        return "";
    }

    /**
     * Remove prohibited characters
     * see #19159
     */
    public static function removeProhibitedCharacters(string $a_text): string
    {
        return str_replace("\x0B", "", $a_text);
    }

    /**
     * Strip slashes with add space fallback, see https://www.ilias.de/mantis/view.php?id=19727
     */
    public function stripSlashesAddSpaceFallback(string $a_str): string
    {
        $str = ilUtil::stripSlashes($a_str);
        if ($str != $a_str) {
            $str = ilUtil::stripSlashes(str_replace("<", "< ", $a_str));
        }
        return $str;
    }

    /**
     * Get label "for" attribute value for filter
     */
    public function getTableFilterLabelFor(): string
    {
        return $this->getFieldId();
    }

    /**
     * Get label "for" attribute value for form
     */
    public function getFormLabelFor(): string
    {
        return $this->getFieldId();
    }

    // get integer parameter kindly
    protected function int($key): int
    {
        if (is_null($this->refinery)) {
            return 0;
        }
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->getRequestParam($key, $t) ?? 0);
    }

    // get integer array kindly
    protected function intArray($key): array
    {
        if (!$this->isRequestParamArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to int
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, (int) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->getRequestParam($key, $t) ?? []);
    }

    // get string parameter kindly
    protected function str($key): string
    {
        if (is_null($this->refinery)) {
            return "";
        }
        $t = $this->refinery->kindlyTo()->string();
        return $this->stripSlashesAddSpaceFallback(
            (string) ($this->getRequestParam($key, $t) ?? "")
        );
    }

    // get raw parameter
    protected function raw($key)
    {
        $t = $this->refinery->custom()->transformation(function ($v) {
            return $v;
        });
        return $this->getRequestParam($key, $t);
    }

    // get string array kindly
    protected function strArray($key): array
    {
        if (!$this->isRequestParamArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        function ($k, $v) {
                            if (is_array($v)) {
                                $v = "";
                            }
                            return [$k, $this->stripSlashesAddSpaceFallback((string) $v)];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->getRequestParam($key, $t) ?? []);
    }

    // get array of arrays kindly
    protected function arrayArray($key): array
    {
        if (!$this->isRequestParamArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, (array) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->getRequestParam($key, $t) ?? []);
    }

    protected function isRequestParamArray(string $key): bool
    {
        $no_transform = $this->refinery->identity();
        $w = $this->http->wrapper();
        if ($w->post()->has($key)) {
            return is_array($w->post()->retrieve($key, $no_transform));
        }
        if ($w->query()->has($key)) {
            return is_array($w->query()->retrieve($key, $no_transform));
        }
        return false;
    }

    /**
     * @return mixed|null
     */
    protected function getRequestParam(string $key, Refinery\Transformation $t)
    {
        $w = $this->http->wrapper();
        if ($w->post()->has($key)) {
            return $w->post()->retrieve($key, $t);
        }
        if ($w->query()->has($key)) {
            return $w->query()->retrieve($key, $t);
        }
        return null;
    }
}
