<?php

/**
 * Interface ilWorkerFactory
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
interface ilWorkerFactory {

	/**
	 * @return ilWorker
	 */
	public function getWorker();
}