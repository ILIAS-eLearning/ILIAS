<?php

/**
 * Class ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBTJobBase {

	/** @var ilBTIO * */
	protected $input;
	/** @var ilBTIO * */
	protected $output;


	/** @return string gets the classname of the input type * */
	abstract function getInputType();


	/** @return string gets the classname of the output type * */
	abstract function getOutputType();
}
