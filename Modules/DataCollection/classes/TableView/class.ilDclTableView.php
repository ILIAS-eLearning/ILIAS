<?php

/**
 * Class ilDclTableView
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableView extends ActiveRecord
{
    /**
     * @var int
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected ?int $id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $table_id = 0;
    /**
     * @var string
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $title = "";
    /**
     * @var array
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected array $roles = array();
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $description = '';
    /**
     * @var int
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $tableview_order = 0;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $step_vs = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $step_c = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $step_e = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $step_o = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $step_s = false;
    /**
     * @var ilDclBaseFieldModel[]
     */
    protected array $visible_fields_cache = [];

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName(): string
    {
        return "il_dcl_tableview";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTableId(): int
    {
        return $this->table_id;
    }

    public function setTableId(int $table_id): void
    {
        $this->table_id = $table_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getOrder(): int
    {
        return $this->tableview_order;
    }

    public function setOrder(int $order): void
    {
        $this->tableview_order = $order;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTableviewOrder(): int
    {
        return $this->tableview_order;
    }

    public function setTableviewOrder(int $tableview_order): void
    {
        $this->tableview_order = $tableview_order;
    }

    public function isStepVs(): bool
    {
        return $this->step_vs;
    }

    public function setStepVs(bool $step_vs): void
    {
        $this->step_vs = $step_vs;
    }

    public function isStepC(): bool
    {
        return $this->step_c;
    }

    public function setStepC(bool $step_c): void
    {
        $this->step_c = $step_c;
    }

    public function isStepE(): bool
    {
        return $this->step_e;
    }

    public function setStepE(bool $step_e): void
    {
        $this->step_e = $step_e;
    }

    public function isStepO(): bool
    {
        return $this->step_o;
    }

    public function setStepO(bool $step_o): void
    {
        $this->step_o = $step_o;
    }

    public function isStepS(): bool
    {
        return $this->step_s;
    }

    public function setStepS(bool $step_s): void
    {
        $this->step_s = $step_s;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param string $field_name
     */
    public function sleep($field_name): ?string
    {
        if ($field_name == 'roles') {
            return json_encode($this->roles);
        }

        return null;
    }

    /**
     * @param string     $field_name
     * @param int|string $field_value
     */
    public function wakeUp($field_name, $field_value): ?array
    {
        if ($field_name == 'roles') {
            return json_decode($field_value);
        }

        return null;
    }

    public function delete(): void
    {
        //Delete settings
        foreach ($this->getFieldSettings() as $setting) {
            $setting->delete();
        }
        parent::delete();
    }

    public function getTable(): ilDclTable
    {
        return ilDclCache::getTableCache($this->table_id);
    }

    /**
     * @return ActiveRecord|ilDclTableView
     */
    public static function findOrGetInstance($primary_key, array $add_constructor_args = array()): ActiveRecord
    {
        return parent::findOrGetInstance($primary_key, $add_constructor_args);
    }

    /**
     * getFilterableFields
     * Returns all  fieldsetting-objects of this tableview which have set their filterable to true, including standard fields.
     * @return ilDclBaseFieldModel[]|ilDclTableViewFieldSetting[]
     */
    public function getFilterableFieldSettings(): array
    {
        return ilDclTableViewFieldSetting::where(
            array(
                "tableview_id" => $this->id,
                'in_filter' => 1,
                'il_dcl_tfield_set.table_id' => $this->getTableId(),
            )
        )->innerjoin('il_dcl_tfield_set', 'field', 'field', array())
                                         ->orderBy('il_dcl_tfield_set.field_order')
                                         ->get();
    }

    /**
     * Returns all field-objects of this tableview which have set their visibility to true, including standard fields.
     * @return ilDclBaseFieldModel[]|ilDclTableViewFieldSetting[]
     */
    public function getVisibleFields(): array
    {
        if (!$this->visible_fields_cache) {
            $visible = ilDclTableViewFieldSetting::where(
                array(
                    "tableview_id" => $this->id,
                    'visible' => true,
                    'il_dcl_tfield_set.table_id' => $this->getTableId(),
                )
            )->innerjoin(
                'il_dcl_tfield_set',
                'field',
                'field',
                array()
            )->orderBy('il_dcl_tfield_set.field_order')->get();
            $fields = array();
            foreach ($visible as $field_rec) {
                $fields[] = $field_rec->getFieldObject();
            }
            $this->visible_fields_cache = $fields;
        }

        return $this->visible_fields_cache;
    }

    /**
     * @return ilDclTableViewFieldSetting[]
     * @throws arException
     */
    public function getFieldSettings(): array
    {
        return ilDclTableViewFieldSetting::where(
            array(
                'tableview_id' => $this->getId(),
                'il_dcl_tfield_set.table_id' => $this->getTableId(),
            )
        )->innerjoin('il_dcl_tfield_set', 'field', 'field', array('field_order'))->orderBy('field_order')->get();
    }

    /**
     * @param $field_id
     * @return ilDclTableViewFieldSetting|ActiveRecord
     */
    public function getFieldSetting($field_id): ActiveRecord
    {
        return ilDclTableViewFieldSetting::where([
            'tableview_id' => $this->getId(),
            'field' => $field_id
        ])->first();
    }

    public function create(bool $create_default_settings = true): void
    {
        parent::create();
        if ($create_default_settings) {
            $this->createDefaultSettings();
        }
    }

    /**
     * create default ilDclTableViewFieldSetting entries
     */
    public function createDefaultSettings(): void
    {
        $table = ilDclCache::getTableCache($this->table_id);

        foreach ($table->getFieldIds() as $field_id) {
            $this->createFieldSetting($field_id);
        }

        //ilDclTable->getFieldIds won't reuturn comments if they are disabled,
        //still we have to create a fieldsetting for this field
        if (!$table->getPublicCommentsEnabled()) {
            $this->createFieldSetting('comments');
        }
    }

    /**
     * create ilDclTableViewFieldSetting for this tableview and the given field id
     * @param int|string $field_id
     */
    public function createFieldSetting($field_id): void
    {
        if (!ilDclTableViewFieldSetting::where(
            array(
                'tableview_id' => $this->id,
                'field' => $field_id,
            )
        )->get()
        ) {
            $field_set = new ilDclTableViewFieldSetting();
            $field_set->setTableviewId($this->id);
            $field_set->setField($field_id);
            $field_set->setVisible(!ilDclStandardField::_isStandardField($field_id));
            $field_set->setFilterChangeable(true);
            $field_set->setLockedCreate(false);
            $field_set->setLockedEdit(false);
            $field_set->setRequiredCreate(false);
            $field_set->setRequiredEdit(false);
            $field_set->setVisibleCreate(true);
            $field_set->setVisibleEdit(true);
            $field_set->create();
        }
    }

    /**
     * @param ilDclTableView $orig
     * @param array          $new_fields fields mapping
     */
    public function cloneStructure(ilDclTableView $orig, array $new_fields): void
    {
        //clone structure
        $this->setTitle($orig->getTitle());
        $this->setOrder($orig->getOrder());
        $this->setDescription($orig->getDescription());
        $this->setRoles($orig->getRoles());
        $this->setStepVs($orig->isStepVs());
        $this->setStepC($orig->isStepC());
        $this->setStepE($orig->isStepE());
        $this->setStepO($orig->isStepO());
        $this->setStepS($orig->isStepS());
        $this->create(false); //create default setting, adjust them later

        //clone default values
        $f = new ilDclDefaultValueFactory();

        //clone fieldsettings
        foreach ($orig->getFieldSettings() as $orig_fieldsetting) {
            $new_fieldsetting = new ilDclTableViewFieldSetting();
            $new_fieldsetting->setTableviewId($this->getId());
            if ($new_fields[$orig_fieldsetting->getField()] ?? null) {
                //normal fields
                $new_fieldsetting->setField($new_fields[$orig_fieldsetting->getField()]->getId());
            } else {
                //standard fields
                $new_fieldsetting->setField($orig_fieldsetting->getField());
            }
            $new_field_id = $new_fieldsetting->cloneStructure($orig_fieldsetting);

            //clone default value
            $datatype = $orig_fieldsetting->getFieldObject()->getDatatypeId();
            $match = ilDclTableViewBaseDefaultValue::findSingle($datatype, $orig_fieldsetting->getId());

            if (!is_null($match)) {
                $new_default_value = $f->create($datatype);
                $new_default_value->setTviewSetId($new_field_id);
                $new_default_value->setValue($match->getValue());
                $new_default_value->create();
            }
        }

        //clone pageobject
        if (ilDclDetailedViewDefinition::exists($orig->getId())) {
            $orig_pageobject = new ilDclDetailedViewDefinition($orig->getId());
            $orig_pageobject->copy($this->getId());
        }

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($orig->getId(), $this->getId(), ilDclCache::TYPE_TABLEVIEW);
    }

    /**
     * @return ilDclTableView[]|ActiveRecord[]
     */
    public static function getAllForTableId(int $table_id): array
    {
        return self::where(array('table_id' => $table_id))->orderBy('tableview_order')->get();
    }

    public static function getCountForTableId(int $table_id): int
    {
        return self::where(array('table_id' => $table_id))->orderBy('tableview_order')->count();
    }

    /**
     * @param      $table_id
     * @param bool $create_default_settings
     * @return ilDclTableView|ActiveRecord
     */
    public static function createOrGetStandardView(int $table_id, bool $create_default_settings = true): ActiveRecord
    {
        if ($standardview = self::where(array('table_id' => $table_id))->orderBy('tableview_order')->first()) {
            return $standardview;
        }

        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $http = $DIC->http();
        $refinery = $DIC->refinery();

        $roles = array();

        $ref_id = $http->wrapper()->query()->retrieve('ref_id', $refinery->kindlyTo()->int());
        foreach ($rbacreview->getParentRoleIds($ref_id) as $role_array) {
            $roles[] = $role_array['obj_id'];
        }

        $view = new self();

        $hasRefId = $http->wrapper()->query()->has('ref_id');

        if ($hasRefId) {
            global $DIC;
            $rbacreview = $DIC['rbacreview'];

            $ref_id = $http->wrapper()->query()->retrieve('ref_id', $refinery->kindlyTo()->int());

            $roles = array();
            foreach ($rbacreview->getParentRoleIds($ref_id) as $role_array) {
                $roles[] = $role_array['obj_id'];
            }
            $view->setRoles(array_merge($roles, $rbacreview->getLocalRoles($ref_id)));
        }
        $view->setTableId($table_id);
        // bugfix mantis 0023307
        $lng = $DIC['lng'];
        $view->setTitle($lng->txt('dcl_title_standardview'));
        $view->setTableviewOrder(10);
        $view->setStepVs(true);
        $view->setStepC(false);
        $view->setStepE(false);
        $view->setStepO(false);
        $view->setStepS(false);
        $view->create($create_default_settings);

        return $view;
    }

    /**
     * Check if the configuration of the view is complete. The step "single" is
     * optional and therefore omitted.
     */
    public function validateConfigCompletion(): bool
    {
        return $this->step_vs && $this->step_c && $this->step_e && $this->step_o;
    }
}
