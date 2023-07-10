<?php

declare(strict_types=1);

/**
 * This is a ADT-based example object
 * It has all supported ADTs and shows DB sequence-handling
 */
class ilADTTest extends ilADTBasedObject
{
    protected int $id;
    protected ilADT $properties;

    public const INTERESTS_NONE = 0;
    public const INTERESTS_LANGUAGES = 1;
    public const INTERESTS_IT = 2;

    // properties

    protected function initProperties(): ilADT
    {
        // this could be generated from XML or code comments or whatever

        $factory = ilADTFactory::getInstance();

        $properties_def = $factory->getDefinitionInstanceByType("Group");

        $name = $factory->getDefinitionInstanceByType("Text");
        $name->setMaxLength(255);
        $properties_def->addElement("name", $name);

        $status = $factory->getDefinitionInstanceByType("Boolean");
        $properties_def->addElement("active", $status);

        // example options from ilLanguage
        $this->lng->loadLanguageModule("meta");
        $options = array();
        foreach ($this->lng->getInstalledLanguages() as $lang) {
            $options[$lang] = $this->lng->txt("meta_l_" . $lang);
        }

        $lang = $factory->getDefinitionInstanceByType("Enum");
        $lang->setNumeric(false);
        $lang->setOptions($options);
        $properties_def->addElement("lang", $lang);

        $age = $factory->getDefinitionInstanceByType("Integer");
        $age->setMin(0);
        $age->setMax(120);
        $properties_def->addElement("age", $age);

        $weight = $factory->getDefinitionInstanceByType("Float");
        $weight->setMin(0);
        $weight->setMax(500);
        $properties_def->addElement("weight", $weight);

        // null?
        $home = $factory->getDefinitionInstanceByType("Location");
        $properties_def->addElement("home", $home);

        $tags = $factory->getDefinitionInstanceByType("MultiText");
        $tags->setMaxLength(255);
        $tags->setMaxSize(5);
        $properties_def->addElement("tags", $tags);

        $options = array(
            self::INTERESTS_NONE => $this->lng->txt("test_interests_none"),
            self::INTERESTS_LANGUAGES => $this->lng->txt("test_interests_languages"),
            self::INTERESTS_IT => $this->lng->txt("test_interests_it")
        );

        $intr = $factory->getDefinitionInstanceByType("MultiEnum");
        $intr->setOptions($options);
        $properties_def->addElement("interests", $intr);

        $date = $factory->getDefinitionInstanceByType("Date");
        $properties_def->addElement("entry_date", $date);

        $dt = $factory->getDefinitionInstanceByType("DateTime");
        $properties_def->addElement("last_login", $dt);

        // convert ADT definitions to proper ADTs
        return $factory->getInstanceByDefinition($properties_def);
    }


    // CRUD/DB

    // simple sequence example

    protected function initDBBridge(ilADTDBBridge $a_adt_db): void
    {
        $a_adt_db->setTable("adt_test");
        $a_adt_db->setPrimary(array("id" => array("integer", $this->id)));
    }

    protected function parsePrimary(array $a_args): void
    {
        $this->id = (int) $a_args[0];
    }

    protected function hasPrimary(): bool
    {
        return (bool) $this->id;
    }

    protected function createPrimaryKeyb(): bool
    {
        $this->id = $this->db->nextId("adt_test");

        // INSERT is only done if createPrimaryKey() returns TRUE!
        return true;
    }
}
