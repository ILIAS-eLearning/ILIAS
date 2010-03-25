<?php
/**
* @package		nameParser
* @version		1.1
* @author		Greg Miernicki, Keith Beckman, DLM
* LastModified:		2009:0128
* License:		LGPL
* @link			http://code.google.com/p/nameparser/
*/

class ilFullnameParser 
{

	/**
	* Array of possible name titles.
	* @var	array
	*/
	private $titles;

	/**
	* Array of possible last name prefixes.
	* @var	array
	*/
	private $prefices;

	/**
	* Array of possible name suffices.
	* @var	array;
	*/
	private $suffices;

	/**
	* The TITLE ie. Dr., Mr. Mrs., etc...
	* @var	string
	*/
	private $title;

	/**
	* The FIRST Name
	* @var	string
	*/
	private $first;

	/**
	* The MIDDLE Name
	* @var	string
	*/
	private $middle;

	/**
	* The LAST Name
	* @var	string
	*/
	private $last;

	/**
	* Name addendum ie. III, Sr., etc...
	* @var	string
	*/
	private $suffix;

	/**
	* Full name string passed to class
	* @var	string
	*/
	private $fullName;

	/**
	* Set to false by default, but set to true if parse() is executed on a name that is not parseable
	* @var	boolean
	*/
	private $notParseable;


	/**
	* Constructor:
	* Setup the object, initialise the variables, and if instantiated with a name - parse it automagically
	*
	* @param string The Name String
	* @access public
	*/
	public function	__construct( $initString = "" ) {
		$this->title 		= "";
		$this->first 		= "";
		$this->middle 		= "";
		$this->last 		= "";
		$this->suffix 		= "";

		//$this->titles		= array('dr','doctor','miss','misses','mr','mister','mrs','ms','judge','sir','madam','madame');

		// added Military Titles
		$this->titles		= array('dr','doctor','miss','misses','mr','mister','mrs','ms','judge','sir','madam','madame','AB','2ndLt','Amn','1stLt','A1C','Capt','SrA','Maj','SSgt','LtCol','TSgt','Col','BrigGen','1stSgt','MajGen','SMSgt','LtGen','1stSgt','Gen','CMSgt','1stSgt','CCMSgt','CMSAF','PVT','2LT','PV2','1LT','PFC','CPT','SPC','MAJ','CPL','LTC','SGT','COL','SSG','BG','SFC','MG','MSG','LTG','1SGT','GEN','SGM','CSM','SMA','WO1','WO2','WO3','WO4','WO5','ENS','SA','LTJG','SN','LT','PO3','LCDR','PO2','CDR','PO1','CAPT','CPO','RADM(LH)','SCPO','RADM(UH)','MCPO','VADM','MCPOC','ADM','MPCO-CG','CWO-2','CWO-3','CWO-4','Pvt','2ndLt','PFC','1stLt','LCpl','Capt','Cpl','Maj','Sgt','LtCol','SSgt','Col','GySgt','BGen','MSgt','MajGen','1stSgt','LtGen','MGySgt','Gen','SgtMaj','SgtMajMC','WO-1','CWO-2','CWO-3','CWO-4','CWO-5','ENS','SA','LTJG','SN','LT','PO3','LCDR','PO2','CDR','PO1','CAPT','CPO','RDML','SCPO','RADM','MCPO','VADM','MCPON','ADM','FADM','WO1','CWO2','CWO3','CWO4','CWO5');

		$this->prefices		= array('bon','ben','bin','da','dal','de','del','der','de','e','la','le','san','st','ste','van','vel','von');
		$this->suffices		= array('esq','esquire','jr','sr','2','i','ii','iii','iv','v','clu','chfc','cfp','md','phd');
		$this->fullName		= "";
		$this->notParseable 	= FALSE;

		// if initialized by value, set class variable and then parse
		if ( $initString != "" ) {
			$this->fullName = $initString;
			$this->parse();
		}
	}


	/**
	* Destructor
	* @access public
	*/
	public function __destruct() {}



	/**
	* Access Method
	* @access public
	*/
	public function	getFirstName() { return $this->first; }



	/**
	* Access Method
	* @access public
	*/
	public function	getMiddleName() { return $this->middle; }



	/**
	* Access Method
	* @access public
	*/
	public function	getLastName() { return $this->last; }



	/**
	* Access Method
	* @access public
	*/
	public function	getTitle() { return $this->title; }



	/**
	* Access Method
	* @access public
	*/
	public function	getSuffix() { return $this->suffix; }



	/**
	* Access Method
	* @access public
	*/
	public function	getNotParseable() { return $this->notParseable; }



	/**
	* Mutator Method
	* @access public
	* @param newFullName the new value to set fullName to
	*/
	public function	setFullName( $newFullName ) { $this->fullName = $newFullName; }



	/**
	* Determine if the needle is in the haystack.
	*
	* @param needle the needle to look for
	* @param haystack the haystack from which to look into
	* @access private
	*/
	private function inArrayNorm( $needle, $haystack ) {
		$needle = trim( strtolower( str_replace( '.', '', $needle ) ) );
		return	in_array( $needle, $haystack );
	}



	/**
	* Extract the elements of the full name into separate parts.
	*
	* @access public
	*/
	public function	parse() {
		// reset values
		$this->title 		= "";
		$this->first 		= "";
		$this->middle 		= "";
		$this->last 		= "";
		$this->suffix 		= "";
		$this->notParseable 	= FALSE;

		// break up name based on number of commas
		$pieces		= explode( ',', preg_replace('/\s+/', ' ', trim( $this->fullName ) ) );
		$numPieces 	= count( $pieces );

		switch ( $numPieces ) {

			// array(title first middle last suffix)
			case	1:
				$subPieces = explode(' ', trim( $pieces[0] ) );
				$numSubPieces = count( $subPieces );
				for ( $i = 0; $i < $numSubPieces; $i++ ) {
					$current = trim( $subPieces[$i] );
					if ( $i < ($numSubPieces-1) ) {
						$next = trim( $subPieces[$i+1] );
					} else {
						$next = "";
					}
					if ( $i == 0 && $this->inArrayNorm( $current, $this->titles ) ) {
						$this->title = $current;
						continue;
					}
					if ( $this->first == "" ) {
						$this->first = $current;
						continue;
					}
					if ( $i == $numSubPieces-2 && ($next != "") && $this->inArrayNorm( $next, $this->suffices ) ) {
						if ( $this->last != "") {
							$this->last	.=	" ".$current;
						} else {
							$this->last = $current;
						}
						$this->suffix = $next;
						break;
					}
					if ( $i == $numSubPieces-1 ) {
						if ( $this->last != "" ) {
							$this->last .= " ".$current;
						} else {
							$this->last = $current;
						}
						continue;
					}
					if ( $this->inArrayNorm( $current, $this->prefices ) ) {
						if ( $this->last != "" ) {
							$this->last .= " ".$current;
						} else {
							$this->last = $current;
						}
						continue;
					}
					if ( $next == 'y' || $next == 'Y' ) {
						if ( $this->last != "" ) {
							$this->last .= " ".$current;
						} else {
							$this->last = $current;
						}
						continue;
					}
					if ( $this->last != "" ) {
						$this->last .= " ".$current;
						continue;
					}
					if( $this->middle != "" ) {
						$this->middle .= " ".$current;
					} else {
						$this->middle =	$current;
					}
				}
				break;

			default:
				switch( $this->inArrayNorm( $pieces[1], $this->suffices ) ) {

					// array(title first middle last, suffix [, suffix])
					case	TRUE:
						$subPieces = explode(' ', trim( $pieces[0] ) );
						$numSubPieces =	count( $subPieces );
						for ( $i = 0; $i < $numSubPieces; $i++ ) {
							$current = trim( $subPieces[$i] );
							if ( $i < ($numSubPieces-1) ) {
								$next = trim( $subPieces[$i+1] );
							} else {
								$next = "";
							}
							if ( $i == 0 && $this->inArrayNorm( $current, $this->titles ) ) {
								$this->title = $current;
								continue;
							}
							if ( $this->first == "" ) {
								$this->first = $current;
								continue;
							}
							if ( $i == $numSubPieces-1 ) {
								if ( $this->last != "" ) {
									$this->last .=	" ".$current;
								} else {
									$this->last = $current;
								}
								continue;
							}
							if ( $this->inArrayNorm( $current, $this->prefices ) ) {
								if ( $this->last != "" ) {
									$this->last .= " ".$current;
								} else {
									$this->last = $current;
								}
								continue;
							}
							if ( $next == 'y' || $next == 'Y' ) {
								if ( $this->last != "" ) {
									$this->last .= " ".$current;
								} else {
									$this->last = $current;
								}
								continue;
							}
							if ( $this->last != "" ) {
								$this->last .= " ".$current;
								continue;
							}
							if ( $this->middle != "" ) {
								$this->middle .= " ".$current;
							} else {
								$this->middle = $current;
							}
						}
						$this->suffix =	trim($pieces[1]);
						for ( $i = 2; $i < $numPieces; $i++ ) {
							$this->suffix .= ", ". trim( $pieces[$i] );
						}
						break;

					// array(last, title first middles[,] suffix [,suffix])
					case	FALSE:
						$subPieces = explode( ' ', trim( $pieces[1] ) );
						$numSubPieces =	count( $subPieces );
						for ( $i = 0; $i < $numSubPieces; $i++ ) {
							$current = trim( $subPieces[$i] );
							if ( $i < ($numSubPieces-1) ) {
								$next = trim( $subPieces[$i+1] );
							} else {
								$next = "";
							}
							if ( $i == 0 && $this->inArrayNorm( $current, $this->titles ) ) {
								$this->title = $current;
								continue;
							}
							if ( $this->first == "" ) {
								$this->first = $current;
								continue;
							}
							if ( $i == $numSubPieces-2 && ($next != "") && $this->inArrayNorm( $next, $this->suffices ) ) {
								if ( $this->middle != "" ) {
									$this->middle .= " ".$current;
								} else {
									$this->middle = $current;
								}
								$this->suffix = $next;
								break;
							}
							if ( $i == $numSubPieces-1 && $this->inArrayNorm( $current, $this->suffices ) ) {
								$this->suffix = $current;
								continue;
							}
							if ( $this->middle != "" ) {
								$this->middle .= " ".$current;
							} else {
								$this->middle = $current;
							}
						}
						if( isset($pieces[2]) && $pieces[2] ) {
							if ( $this->last == "" ) {
								$this->suffix = trim( $pieces[2] );
								for ($s = 3; $s < $numPieces; $s++) {
									$this->suffix .= ", ". trim( $pieces[$s] );
								}
							} else {
								for ($s = 2; $s < $numPieces; $s++) {
									$this->suffix .= ", ". trim( $pieces[$s] );
								}
							}
						}
						$this->last = $pieces[0];
						break;
				}
				unset( $pieces );
				break;
		}
		if ( $this->first == "" && $this->middle == "" && $this->last == "" ) {
			$this->notParseable = TRUE;
		}
	}
}