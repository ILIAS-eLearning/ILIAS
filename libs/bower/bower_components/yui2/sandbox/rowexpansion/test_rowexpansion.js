(function(){

	YAHOO.namespace("example.yuitest");

	var	YTest         = YAHOO.example.yuitest,
			Ytool         = YAHOO.tool,
			Event         = YAHOO.util.Event,
			Dom           = YAHOO.util.Dom,
			DT            = YAHOO.widget.DataTable,
			Assert        = YAHOO.util.Assert,
			ArrayAssert   = YAHOO.util.ArrayAssert,
			ObjectAssert  = YAHOO.util.ObjectAssert;

	YTest.RowExpansionCoreSuite = new Ytool.TestSuite("RowExpansion Core");

	YTest.RowExpansionCoreSuite.tableMaker = function( oArgs ){
		
		var args     = oArgs || {},
				template = args.rowExpansionTemplate || '{image_url}',
				id       = args.id || 'testTable';

		var myData = [
			{id:"po-0167", date:new Date(1980, 2, 24), quantity:1, amount:4, title:"A Book About Nothing",
			description: "Lorem ipsum dolor sit amet consectetuer Quisque ipsum suscipit Aenean ligula. Accumsan molestie nibh dui orci vitae auctor nec pulvinar ligula elit.",image_url:"book1.gif"},
			{id:"po-0783", date:new Date("January 3, 1983"), quantity:null, amount:12.12345, title:"The Meaning of Life",
			description: "Vestibulum scelerisque wisi adipiscing turpis odio Phasellus euismod id orci tristique. Hendrerit sem dictum volutpat cursus pretium dui vitae tincidunt Vivamus Aenean."},
			{id:"po-0297", date:new Date(1978, 11, 12), quantity:12, amount:1.25, title:"This Book Was Meant to Be Read Aloud",
			description: "Malesuada pellentesque nibh magna nisl tincidunt wisi dui Nam nunc convallis. Adipiscing leo augue Nulla tellus nec eros metus cursus pretium Sed.",image_url:"book2.gif"},
			{id:"po-1482", date:new Date("March 11, 1985"), quantity:6, amount:3.5, title:"Read Me Twice",
			description: "Libero justo pede nibh tincidunt ut tempus metus et Vestibulum vel. Sem justo morbi lacinia dui turpis In Lorem dictumst volutpat cursus.",image_url:"book3.gif"}
    ];

		var myColumnDefs = [
				{
					key:"date",
					sortable:true,
					sortOptions:{
						defaultDir:YAHOO.widget.DataTable.CLASS_DESC
					},
					resizeable:true
				},
				{
					key:"quantity",
					formatter:YAHOO.widget.DataTable.formatNumber,
					sortable:true,
					resizeable:true
				},
				{
					key:"amount",
					formatter:YAHOO.widget.DataTable.formatCurrency,
					sortable:true,
					resizeable:true
				},
				{
					key:"title",
					sortable:true,
					resizeable:true
				}

		];

		var myDataSource = new YAHOO.util.DataSource( myData );
				myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
				myDataSource.responseSchema = {
					fields: [ "id","date","quantity","amount","title","image_url" ]
				};

		var makeDiv = function(){

			var new_div = document.createElement( 'div' );
			new_div.id = id;
			return document.getElementsByTagName( 'body' )[ 0 ].appendChild( new_div );

		};

		var myDataTable = new YAHOO.widget.RowExpansionDataTable(
				( Dom.get( id ) || makeDiv() ),
				myColumnDefs,
				myDataSource,
					{ rowExpansionTemplate : template }
				),

				columns = myDataTable.getColumnSet().flat,
				records = myDataTable.getRecordSet().getRecords(),
				record_ids = [];

		for( var i=0,l=records.length; l > i; i++ ){

			record_ids.push( records[ i ] );

		};

		return { oDT : myDataTable, oDS : myDataSource, eContainer : Dom.get( 'testTable' ), aIds : record_ids, aCols : columns }

	};

	YTest.RowExpansionCoreSuite.tableDestroyer = function( oTable ){

		oTable.oDT.destroy();
		if( oTable.eContainer ){
			
			oTable.eContainer.parentNode.removeChild( oTable.eContainer );
			
		}

	};

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "rowExpansionTemplate Attribute Tests",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();
			this.data_table = this.table.oDT;

		},

		testAttribute : function () {

			ObjectAssert.hasProperty(
				'rowExpansionTemplate',
				this.data_table.configs,
				'DataTable instance is missing the "rowExpansionTemplate" attribute'
			);

		},

		testHasToogleMethod : function () {

			Assert.isFunction(
				this.data_table.toggleRowExpansion,
				'The "toggleRowExpansion" method is not a funcition'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "Pass bad templates to the table",

		setUp : function () {

			this.table_number = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_number', rowExpansionTemplate : 123 } );
			
			this.table_empty = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_nothing', rowExpansionTemplate : '' } );
			
			this.table_object = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_object', rowExpansionTemplate : {} } );
			
			this.table_null = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_null', rowExpansionTemplate : null } );
			
			this.table_undefined = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_undefined', rowExpansionTemplate : undefined } );
			
			this.table_function_true = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_function_true', rowExpansionTemplate : function(){return true} } );
			
			this.table_function_false = YTest.RowExpansionCoreSuite.tableMaker( { id : 'yuitest_table_function_false', rowExpansionTemplate : function(){return false} } );

		},

		testTableWithNumberPassedAsTemplate : function () {

			Assert.isObject(
				this.table_number.oDT,
				'Number DataTable does not appear to be instantiated'
			);
			
			Assert.areSame(
				'TBODY',
				this.table_number.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},

		testTableWithNothingPassedAsTemplate : function () {

			Assert.isObject(
				this.table_empty.oDT,
				'DataTable does not appear to be instantiated'
			);
			
			Assert.areSame(
				'TBODY',
				this.table_empty.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},
		
		testTableWithObjectPassedAsTemplate : function () {

			Assert.isObject(
				this.table_object.oDT,
				'DataTable does not appear to be instantiated'
			);
			
			Assert.areSame(
				'TBODY',
				this.table_object.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},
		
		testTableWithNullPassedAsTemplate : function () {

			Assert.isObject(
				this.table_null.oDT,
				'DataTable does not appear to be instantiated'
			);
			
			Assert.areSame(
				'TBODY',
				this.table_null.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		}	,

		testTableWithUndefinedPassedAsTemplate : function () {

			Assert.isObject(
				this.table_undefined.oDT,
				'DataTable does not appear to be instantiated'
			);

			Assert.areSame(
				'TBODY',
				this.table_undefined.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},

		testTableWithFunctionTruePassedAsTemplate : function () {

			Assert.isObject(
				this.table_function_true.oDT,
				'DataTable does not appear to be instantiated'
			);

			Assert.areSame(
				'TBODY',
				this.table_function_true.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},

		testTableWithFunctionFalsePassedAsTemplate : function () {

			Assert.isObject(
				this.table_function_false.oDT,
				'DataTable does not appear to be instantiated'
			);

			Assert.areSame(
				'TBODY',
				this.table_function_false.oDT.getBody().nodeName,
				'No TBODY element found'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table_number );
			
			YTest.RowExpansionCoreSuite.tableDestroyer( this.table_empty );
			
			YTest.RowExpansionCoreSuite.tableDestroyer( this.table_object );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "toggleRowExpansion (Open) Method",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();
			this.data_table = this.table.oDT;

			//Expand the first row
			this.data_table.toggleRowExpansion( 0 );

		},

		testIsOpen : function () {

			Assert.isTrue(
				Dom.hasClass( this.data_table.getRow(0), 'yui-dt-expanded' ),
				'The first row does not have the "yui-dt-expanded" class applied'
			);

			Assert.isTrue(
				Dom.hasClass( Dom.getNextSibling( this.data_table.getRow(0) ), 'yui-dt-expansion' ),
				'The first row does not have the "yui-dt-expanded" class applied'
			);

			ArrayAssert.isNotEmpty(
				this.data_table.a_rowExpansions,
				'The "a_rowExpansions" array is empty'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "toggleRowExpansion (Closed) Method",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();
			this.data_table = this.table.oDT;

			//Expand and Collapse the first row
			this.data_table.toggleRowExpansion( 0 );
			this.data_table.toggleRowExpansion( 0 );

		},

		testIsClosed : function () {

			Assert.isFalse(
				Dom.hasClass( this.data_table.getRow( 0 ), 'yui-dt-expanded' ),
				'The first row should not have the "yui-dt-expanded" class applied'
			);

			Assert.isFalse(
				Dom.hasClass( Dom.getNextSibling( this.data_table.getRow( 0 ) ), 'yui-dt-expansion' ),
				'The first row should not have the "yui-dt-expanded" class applied'
			);
			
			ArrayAssert.isEmpty(
				this.data_table.a_rowExpansions,
				'The "a_rowExpansions" array is not empty'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "expandRow Method",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();

			this.data_table = this.table.oDT;

			//Expand the first record
			this.data_table.expandRow( this.table.aIds[ 0 ] );

			//Expand the third record then delete the record and restore it
			this.data_table.expandRow( this.table.aIds[ 2 ] );
			var expansion = Dom.getNextSibling( this.data_table.getRow( this.table.aIds[ 2 ] ) );
			expansion.parentNode.removeChild( expansion );
			this.data_table.expandRow( this.table.aIds[ 2 ], true );

		},

		testRegularExpansion : function () {

			Assert.isTrue(
				Dom.hasClass( this.data_table.getRow( this.table.aIds[ 0 ] ), 'yui-dt-expanded' ),
				'The first record does not have the "yui-dt-expanded" class applied'
			);

			Assert.isTrue(
				Dom.hasClass( Dom.getNextSibling( this.data_table.getRow( this.table.aIds[ 0 ] ) ), 'yui-dt-expansion' ),
				'The first record does not have the "yui-dt-expanded" class applied'
			);

		},

		testRestoredExpansion : function () {

			Assert.isTrue(
				Dom.hasClass( this.data_table.getRow( this.table.aIds[ 2 ] ), 'yui-dt-expanded' ),
				'The third record does not have the "yui-dt-expanded" class applied'
			);

			Assert.isTrue(
				Dom.hasClass( Dom.getNextSibling( this.data_table.getRow( this.table.aIds[ 2 ] ) ), 'yui-dt-expansion' ),
				'The third record does not have the "yui-dt-expanded" class applied'
			);

			Assert.isFalse(
				Dom.hasClass( Dom.getNextSibling( Dom.getNextSibling( this.data_table.getRow( this.table.aIds[ 2 ] ) ) ), 'yui-dt-expansion' ),
				'An extra row with "yui-dt-expanded" class applied. Restore failure.'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "collapseAllRows Method",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();

			this.data_table = this.table.oDT;

			//Expand the first and third records
			this.data_table.expandRow( this.table.aIds[ 0 ].getId() );
			this.data_table.expandRow( this.table.aIds[ 1 ].getId() );
			
			/*
			* After calling the collapseAllRows method, there should be no row
			* expansions in the table but the state objects should remain with an 
			* expanded state
			*/
			
			this.data_table.collapseAllRows();

		},

		testIsNotExpanded : function () {

			ArrayAssert.doesNotContain(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expansion' ),
				'There are rows with "yui-dt-expansion" in the DataTable instance'
			);
			
			ArrayAssert.doesNotContain(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expanded' ),
				'There are rows with class "yui-dt-expanded" in this DataTable instance'
			);

			ArrayAssert.isEmpty(
				this.data_table.a_rowExpansions,
				'The "a_rowExpansions" array is not empty'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );

	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "restoreExpandedRows Method",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();

			this.data_table = this.table.oDT;

			//Expand the first and third records
			this.data_table.expandRow( this.table.aIds[ 0 ].getId() );
			this.data_table.expandRow( this.table.aIds[ 1 ].getId() );
			
			/*
			* After calling the collapseAllRows method, there should be no row
			* expansions in the table but the state objects should remain with an 
			* expanded state
			*/
			
			var expansion_rows = Dom.getElementsByClassName( 
				'yui-dt-expansion',
				'tr',
				this.data_table.getBody()
			);
			
			for( var i=0,l=expansion_rows.length;l > i; i++ ){

				var expansion_row = expansion_rows[ i ];

				Dom.replaceClass(
					Dom.getPreviousSibling( expansion_row ),
					'yui-dt-expanded',
					'yui-dt-collapsed'
				);

				expansion_row.parentNode.removeChild( expansion_row );

			}
			
			this.data_table.restoreExpandedRows();

		},

		testIsReExpanded : function () {

			ArrayAssert.contains(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expansion' ),
				'There are not rows with "yui-dt-expansion" in the DataTable instance'
			);
			
			ArrayAssert.contains(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expanded' ),
				'There are not rows with class "yui-dt-expanded" in this DataTable instance'
			);

			ArrayAssert.isNotEmpty(
				this.data_table.a_rowExpansions,
				'The "a_rowExpansions" array is empty'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );
	
	YTest.RowExpansionCoreSuite.add( new Ytool.TestCase({

		name : "Sort Table",

		setUp : function () {

			this.table = YTest.RowExpansionCoreSuite.tableMaker();

			this.data_table = this.table.oDT;

			//Expand the first and third records
			this.data_table.expandRow( this.table.aIds[ 0 ].getId() );
			this.data_table.expandRow( this.table.aIds[ 1 ].getId() );
			
			//Sort column
			this._columnHasSorted = false;
			
			this.data_table.subscribe( 'columnSortEvent', function(){ //The API doc says this is "Fired when a column is sorted"
				this._columnHasSorted = true;
			},null,this);
			
			this.data_table.sortColumn( this.table.aCols[0], YAHOO.widget.DataTable.CLASS_DESC );
			
			//Restore rows
			this.data_table.restoreExpandedRows();
			
		},

		testSortCompleted : function () {
			
			Assert.isTrue(
				this._columnHasSorted,
				"Column has not been sorted"
			);

		},

		testIsReExpanded : function () {

			ArrayAssert.contains(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expansion' ),
				'There are not rows with "yui-dt-expansion" in the DataTable instance'
			);
			
			ArrayAssert.contains(
				true,
				Dom.hasClass( this.data_table.getBody().getElementsByTagName( 'tr' ), 'yui-dt-expanded' ),
				'There are not rows with class "yui-dt-expanded" in this DataTable instance'
			);

			ArrayAssert.isNotEmpty(
				this.data_table.a_rowExpansions,
				'The "a_rowExpansions" array is empty'
			);

		},

		tearDown : function () {

			YTest.RowExpansionCoreSuite.tableDestroyer( this.table );

		}

	}) );


	Event.onDOMReady(function (){
		//create the logger
		var logger = new Ytool.TestLogger("testLogger");
		Ytool.TestRunner.add( YTest.RowExpansionCoreSuite );

		//run the tests
		Ytool.TestRunner.run();
	});

})();