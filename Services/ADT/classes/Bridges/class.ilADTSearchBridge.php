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

use ILIAS\HTTP\Services as HttpServices;

/**
 * ADT search bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTSearchBridge
{
    public const SQL_STRICT = 1;
    public const SQL_LIKE = 2;
    public const SQL_LIKE_END = 3;
    public const SQL_LIKE_START = 4;
    public const DEFAULT_SEARCH_COLUMN = 'value';

    protected ?ilPropertyFormGUI $form = null;
    protected ilTable2GUI $table_gui;
    protected array $table_filter_fields = [];
    protected string $id = '';
    protected string $title = '';
    protected string $info = '';

    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected HttpServices $http;

    public function __construct(ilADTDefinition $a_adt_def)
    {
        global $DIC;
        $this->setDefinition($a_adt_def);

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->http = $DIC->http();
    }

    abstract protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool;

    abstract protected function setDefinition(ilADTDefinition $a_adt_def): void;

    abstract public function isNull(): bool;

    public function setForm(ilPropertyFormGUI $a_form): void
    {
        $this->form = $a_form;
    }

    public function getForm(): ?ilPropertyFormGUI
    {
        return $this->form;
    }

    public function setElementId(string $a_value): void
    {
        $this->id = $a_value;
    }

    public function getElementId(): string
    {
        return $this->id;
    }

    public function setTitle(string $a_value): void
    {
        $this->title = trim($a_value);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSearchColumn(): string
    {
        return self::DEFAULT_SEARCH_COLUMN;
    }

    public function setTableGUI(ilTable2GUI $a_table): void
    {
        $this->table_gui = $a_table;
    }

    /**
     * Get table gui
     */
    public function getTableGUI(): ?ilTable2GUI
    {
        return $this->table_gui;
    }

    /**
     * Write value(s) to filter store (in session)
     * @param ?$a_value
     */
    protected function writeFilter($a_value = null): void
    {
        if (!$this->table_gui instanceof ilTable2GUI) {
            return;
        }
        $session_table = (array) (ilSession::get("form_" . $this->table_gui->getId()) ?? []);
        if ($a_value !== null) {
            $session_table[$this->getElementId()] = serialize($a_value);
        } else {
            unset($session_table[$this->getElementId()]);
        }
        ilSession::set("form_" . $this->table_gui->getId(), $session_table);
    }

    /**
     * Load value(s) from filter store (in session)
     * @return
     */
    protected function readFilter()
    {
        if (!$this->table_gui instanceof ilTable2GUI) {
            return null;
        }
        $session_table = (array) (ilSession::get("form_" . $this->table_gui->getId()) ?? []);
        $value = $session_table[$this->getElementId()] ?? '';
        if ($value) {
            return unserialize($value);
        }
        return '';
    }

    /**
     * Load filter value(s) into ADT
     */
    abstract public function loadFilter(): void;

    /**
     * Add form field to parent element
     * @param ilFormPropertyGUI $a_field
     */
    protected function addToParentElement(ilFormPropertyGUI $a_field): void
    {
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            $this->getForm()->addItem($a_field);
        } elseif (
            $this->getTableGUI() instanceof ilTable2GUI &&
            $a_field instanceof ilTableFilterItem
        ) {
            $this->table_filter_fields[$a_field->getFieldId()] = $a_field;
            $this->getTableGUI()->addFilterItem($a_field);
        }
    }

    /**
     * Add sub-element
     * @param string $a_add
     * @return string
     */
    protected function addToElementId(string $a_add): string
    {
        return $this->getElementId() . "[" . $a_add . "]";
    }

    /**
     * Add ADT-specific fields to form
     */
    abstract public function addToForm(): void;

    /**
     * Check if incoming values should be imported at all
     * @param string|int $a_post
     * @return bool
     */
    protected function shouldBeImportedFromPost($a_post): bool
    {
        return true;
    }

    /**
     * Extract data from (post) values
     * @param array $a_post
     * @return mixed
     */
    protected function extractPostValues(array $a_post = null)
    {
        $element_id = $this->getElementId();
        $multi = strpos($this->getElementId(), "[");

        // get rid of this case
        if ($a_post === null) {
            $a_post = $this->http->request()->getParsedBody();
            if ($multi !== false) {
                $post = $a_post[substr($element_id, 0, $multi)][substr($element_id, $multi + 1, -1)];
            } else {
                $post = $a_post[$element_id];
            }
        } elseif ($multi !== false) {
            $post = $a_post[substr($element_id, $multi + 1, -1)];
        } else {
            $post = $a_post[$element_id];
        }
        return $post;
    }

    /**
     * @todo make post required
     */
    abstract public function importFromPost(array $a_post = null): bool;

    /**
     * Validate current data
     * @return bool
     */
    abstract public function validate(): bool;


    //
    // DB
    //

    /**
     * Get SQL condition for current value(s)
     */
    abstract public function getSQLCondition(
        string $a_element_id,
        int $mode = self::SQL_LIKE,
        array $quotedWords = []
    ): string;

    /**
     * Compare directly against ADT
     */
    public function isInCondition(ilADT $a_adt): bool
    {
        return false;
    }


    //
    //  import/export
    //

    /**
     * Get current value(s) in serialized form (for easy persisting)
     */
    abstract public function getSerializedValue(): string;

    /**
     * Set current value(s) in serialized form (for easy persisting)
     */
    abstract public function setSerializedValue(string $a_value): void;
}
