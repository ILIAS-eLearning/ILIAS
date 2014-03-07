<?php

/**
 * Class arConnector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 * @description
 */
abstract class arConnector {

	abstract public static function checkConnection(ActiveRecord $ar);


	abstract public static function installDatabase(ActiveRecord $ar, $fields);


	abstract public static function updateDatabase(ActiveRecord $ar);


	abstract public static function resetDatabase(ActiveRecord $ar);


	abstract public static function truncateDatabase(ActiveRecord $ar);


	abstract public static function checkTableExists(ActiveRecord $ar);


	abstract public static function checkFieldExists(ActiveRecord $ar, $field_name);


	abstract public static function removeField(ActiveRecord $ar, $field_name);


    abstract public static function renameField(ActiveRecord $ar, $old_name, $new_name);


	abstract public static function create(ActiveRecord $ar);


	abstract public static function read(ActiveRecord $ar);


	abstract public static function update(ActiveRecord $ar);


	abstract public static function delete(ActiveRecord $ar);
}

?>
