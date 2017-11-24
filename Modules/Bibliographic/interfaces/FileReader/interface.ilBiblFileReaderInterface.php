<?php

/**
 * Interface ilBiblFileReaderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderInterface {

	/**
	 * @param $path_to_file
	 *
	 * @return bool
	 */
	public function readContent($path_to_file);


	/**
	 * @deprecated use
	 * @return array
	 */
	public function parseContent();


	/**
	 * @param \ilObjBibliographic $bib
	 *
	 * @return \ilBiblEntry[]
	 */
	public function parseContentToEntries(ilObjBibliographic $bib);
}