<?php

/**
 * Class arStatement
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.6
 */
abstract class arStatement {

	/**
	 * @param ActiveRecord $ar
	 *
	 * @return string
	 */
	abstract public function asSQLStatement(ActiveRecord $ar);
}

?>
