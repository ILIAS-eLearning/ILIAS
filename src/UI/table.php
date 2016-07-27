<?php

// This is does not actually work, but rather is a mock for a how a table could
// look like in the new ILIAS UI framework.
//
// I use a well known table (the famous user table from the administration) for
// demonstrational purpose.
//
// I also assume some kind of streamlining for tables:
//   - row template will not be used anymore, instead everything is derived
//     from the column definitions
//   - title won't be considered being a table element anymore
//
// There still would be some questions we need to solve:
//   - do we want to explicitly set width of columns? (assume no for the moment,
//     as this is a problem of the rendering?)
//
// When discussing the design-problems with tables with our Art Directory, i
// came to the conclusion that there might be two types of tables we use in the
// system, which should be clearly distinguished.
//
// One is a "data-table" that more or less resembles excel and it's functionality.
// These tables are hard to design, as the amount of columns and other stuff is
// unknown when designing the table. That is why we should not even try to set
// width for the columns but instead let the user resize the tables freely after
// initializing the table with a size that fits the screen. Than it's he, who
// breaks the design when widening columns, not us. We then need a hierarchy for
// the importance of columns, to hide or show them for different screen sizes.
//
// The other type of table is a "presentation-table", that has a clearly defined
// amount of columns and potentially also different types of rows, that would
// make it able to create a detailed design of the table.

class ilObjUserFolderGUI {

	// some stuff here...

	protected function filter() {
		
	}

	protected function table() {
		global $DIC;
		$ui = $DIC->UIFactory();
		$lng = $DIC->lng();
		$f = $ui->tablePart();

		return $ui->table
			( array // columns
				// Use keys for column ids as these are unique per definition.
				// If the column would contain the id, we would need to check
				// whether ids are unique.
				( "login" => $f->important_column	// importance of the columns defined
													//with different constructors
					( $lng->txt("login")
					)
				, 
				)
				
			);
	}

	// some other stuff there...

}

