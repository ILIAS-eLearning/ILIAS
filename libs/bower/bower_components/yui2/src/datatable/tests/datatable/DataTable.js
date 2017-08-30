(function() {

    var gCount = -1;

    var Dom=YAHOO.util.Dom,
        Assert=YAHOO.util.Assert,
        ObjectAssert=YAHOO.util.ObjectAssert,
        ArrayAssert=YAHOO.util.ArrayAssert,
        DateAssert=YAHOO.util.DateAssert,
        UserAction=YAHOO.util.UserAction,
        TestCase = YAHOO.tool.TestCase,
        TestLogger = YAHOO.tool.TestLogger,
        TestRunner = YAHOO.tool.TestRunner,
        TestSuite = YAHOO.tool.TestSuite,

        DataSource = YAHOO.util.DataSource,
        DataTable = YAHOO.widget.DataTable,
        ColumnSet = YAHOO.widget.ColumnSet,
        RecordSet = YAHOO.widget.RecordSet;

    /**
     *
     *
     * DataTable assertions.
     *
     *
     */
    var DataTableAssert = {
        areSameRow: function(elTr, oRecord, dt,  msg) {
            Assert.areSame("tr", elTr.tagName.toLowerCase(), "Expected a TR element: " + msg);
            Assert.areSame(elTr.id, oRecord.getId(), "TR ID and Record IDs don't match: " + msg);
        },
        
        areSameCell: function(elTd, oRecord, oColumn, dt, msg) {
            Assert.areSame("td", elTd.tagName.toLowerCase(), "Expected a TD element: " + msg);
            Assert.areSame(elTd.cellIndex, oColumn.getKeyIndex(), "TR index and Column key index don't match: " + msg);
            this.areSameRow(elTd.parentNode, oRecord, dt, msg);
        }
    };

    /**
     *
     *
     * Base DataTable test case.
     *
     *
     */
    function DataTableTestCase(template) {
        DataTableTestCase.superclass.constructor.call(this, template);
    };
    YAHOO.lang.extend(DataTableTestCase, TestCase);

    DataTableTestCase.prototype.setUp = function() {
        // Create container anew
        this.container = document.createElement("div");
        document.body.appendChild(this.container);
        
        // Create DataSource anew
        this.datasource = new YAHOO.util.DataSource(this.dsData, this.dsConfig);
    };

    DataTableTestCase.prototype.tearDown = function() {
        // Destroy DataTable
        this.datatable.destroy();
        this.datatable = null;
        
        //  Destroy container
        if(this.container !== null) {
            YAHOO.util.Event.purgeElement(this.container, true);
            document.body.removeChild(this.container);
            this.container = null;
        }
        
        // TODO: need a destroy method
        this.datasource = null;
    };

    DataTableTestCase.prototype.createInstance = function(oDT, oConfig, aColumns) {
        oDT = oDT || DataTable;
        aColumns = aColumns || this.columns;
        this.datatable = new oDT(this.container, aColumns, this.datasource, oConfig);
        gCount++;
        return this.datatable;
    };

    DataTableTestCase.prototype.expandRows = function(oDT) {
        var allRows = oDT.getTbodyEl().rows,
            i=allRows.length-1,
            d   = document,
            tr  = d.createElement('tr'),
            td  = d.createElement('td'),
            clone;
            
        td.colSpan = 3;
        tr.appendChild(td);

        for(; i>-1; i--) {
            clone = tr.cloneNode(true);
            clone.firstChild.innerHTML = "expando " + i;
            YAHOO.util.Dom.insertAfter(clone, allRows[i]);
        }
    };

    /**
     *
     *
     * Base DataTable test template. Sets up values for a DataTable instance.
     *
     *
     */
    var dtBaseTemplate = {
        name: "DataTable Base Tests",
        
        dsData: [
            {a:"0a",b:"0b",c:"0c"},
            {a:"1a",b:"1b",c:"1c"},
            {a:"2a",b:"2b",c:"2c"},
            {a:"3a",b:"3b",c:"3c"}
        ],
            
        dsConfig: {
            responseType:YAHOO.util.DataSource.TYPE_JSARRAY,
            responseSchema:{fields:["a","b","c"]}
        },

        columns: [{key:"a"},{key:"b"},{key:"c"}]
    };
    
    /**
     *
     *
     * Tests various construction use cases.
     *
     *
     */
    var dtConstructionTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Construction Tests",

        testConstruction: function() {
            var dt = this.createInstance();

            Assert.isInstanceOf(DataTable, this.datatable, "Failed to create DataTable instance");
            Assert.isInstanceOf(ColumnSet, this.datatable.getColumnSet(), "Failed to create ColumnSet instance");
            Assert.isInstanceOf(RecordSet, this.datatable.getRecordSet(), "Failed to create RecordSet instance");
            Assert.areSame(this.dsData.length, this.datatable.getRecordSet().getLength(), "Unexpected RecordSet length");

        },

        testNestedHeaders: function() {
            //TODO
            var dt = this.createInstance();
        },
        
        testMultipleInstances: function() {
            var multiple = 3; // Set how many instances (total) to create for this test case
            
            // Create first instance
            var dt = this.createInstance();
            var cs = dt.getColumnSet();
            var oColumn = cs.keys[0];

            // Create more instances
            for(var i=1; i<multiple; i++) {
                this["container"+i] = document.createElement("div");
                this["container"+i].id = "testDTContainer"+i;
                document.body.appendChild(this["container"+i]);

                this["datasource"+i] = new YAHOO.util.DataSource(this.dsData, this.dsConfig);
                
                this["dt"+i] = new YAHOO.widget.DataTable(this["container"+i], this.columns, this["datasource"+i]);
                this["cs"+i] = this["dt"+i].getColumnSet();
                gCount++;
            }
            
            // Test getColumn() on first instance
            var el = dt.getTheadEl().rows[0].cells[0];
            var oTestColumn = dt.getColumn(el);
            Assert.areSame(oColumn, oTestColumn, "Expected to get Column by el reference");

            //oTestColumn = dt.getColumn(el.id);
            //Assert.areSame(oColumn, oTestColumn, "Expected to get Column by DOM ID");

            oTestColumn = dt.getColumn(0);
            Assert.areSame(oColumn, oTestColumn, "Expected to get Column by key index");

            oTestColumn = cs.getColumn("a");
            Assert.areSame(oColumn, oTestColumn, "Expected to get Column by key (ColumnSet method)");

            oTestColumn = cs.getColumnById(oTestColumn.getId());
            Assert.areSame(oColumn, oTestColumn, "Expected to get Column by Column ID (ColumnSet method)");
            
            // Test getColumn() on other instances
            for(var i=1; i<multiple; i++) {
                oColumn = this["cs"+i].keys[0];
                
                el = this["dt"+i].getTheadEl().rows[0].cells[0];
                oTestColumn = this["dt"+i].getColumn(el);
                Assert.areSame(oColumn, oTestColumn, "Expected to get Column by el reference");

                //oTestColumn = this["dt"+i].getColumn(el.id);
                //Assert.areSame(oColumn, oTestColumn, "Expected to get Column by DOM ID");

                oTestColumn = this["dt"+i].getColumn(0);
                Assert.areSame(oColumn, oTestColumn, "Expected to get Column by key index");

                oTestColumn = this["cs"+i].getColumn("a");
                Assert.areSame(oColumn, oTestColumn, "Expected to get Column by key");

                oTestColumn = this["cs"+i].getColumnById(oTestColumn.getId());
                Assert.areSame(oColumn, oTestColumn, "Expected to get Column by Column ID");
            }

            // Destroy the other instances (first instance gets destroyed by the tearDown() function)
            for(var i=1; i<3; i++) {
                this["dt"+i].destroy();
                this["dt"+i] = null;

                if (this["container"+i] != null) {
                    YAHOO.util.Event.purgeElement(this["container"+i], true);
                    document.body.removeChild(this["container"+i]);
                }

                this.datasource = null;
            }
        }
    });
    var dtConstructionTest = new DataTableTestCase(dtConstructionTemplate)

    /**
     *
     *
     * Tests DOM element getters.
     *
     *
     */
    var dtDomAccessorsTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable DOM Accessors Tests",

        testGetContainerEl: function() {
            var dt = this.createInstance();

            var elContainer = dt.getContainerEl();
            
            Assert.areSame("div", elContainer.tagName.toLowerCase(), "Expected a DIV element");
            Assert.areSame(true, YAHOO.util.Dom.hasClass(elContainer, "yui-dt"), "Failed to apply yui-dt classname");
        },

        testGetMsgTbodyEl: function() {
            var dt = this.createInstance();
            var elContainer = dt.getContainerEl();
            var elTbody = dt.getMsgTbodyEl();
            
            Assert.areSame("tbody", elTbody.tagName.toLowerCase(), "Expected a TBODY element");
            Assert.areSame(elContainer.lastChild.tBodies[0], elTbody, "Expected to get first TBODY element of the TABLE");
        },

        testGetTbodyEl: function() {
            var dt = this.createInstance();
            var elContainer = dt.getContainerEl();
            var elTbody = dt.getTbodyEl();
            
            Assert.areSame("tbody", elTbody.tagName.toLowerCase(), "Expected a TBODY element");
            Assert.areSame(elContainer.lastChild.tBodies[elContainer.lastChild.tBodies.length-1], elTbody, "Expected to get last TBODY element of the TABLE");
        },

        testGetFirstTrEl: function() {
            var dt = this.createInstance();
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            var elTbody = dt.getContainerEl().lastChild.tBodies[dt.getContainerEl().lastChild.tBodies.length-1];

            Assert.areSame(elTbody.rows[0], elRow, "Expected to get first TR element of the TBODY");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");
        },

        testGetLastTrEl: function() {
            var dt = this.createInstance();
            var elRow = dt.getLastTrEl();
            var oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            var elTbody = dt.getContainerEl().lastChild.tBodies[dt.getContainerEl().lastChild.tBodies.length-1];

            Assert.areSame(elTbody.rows[elTbody.rows.length-1], elRow, "Expected to get last TR element of the TBODY");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");
        },
        
        testGetTrEl: function() {
            var dt = this.createInstance();
            var elRow = dt.getTrEl(dt._oRecordSet._records[0].getId());
            var elFirstTr = dt.getTbodyEl().rows[0];

            Assert.areSame(elFirstTr, elRow, "By DOM ID: TR el mismatch");

            elRow = dt.getTrEl(dt.getTbodyEl().rows[0]);

            Assert.areSame(elFirstTr, elRow, "By el ref: TR el mismatch");

            elRow = dt.getTrEl(0);

            Assert.areSame(elFirstTr, elRow, "Page row index: TR el mismatch");

            elRow = dt.getTrEl(dt._oRecordSet._records[0]);

            Assert.areSame(elFirstTr, elRow, "Record instance: TR el mismatch");
        },

        testGetNextTrEl: function() {
            var dt = this.createInstance();
            var elFirstTr = dt.getTbodyEl().rows[0];
            var elSecondTr = dt.getTbodyEl().rows[1];
            var elTr = dt.getNextTrEl(elFirstTr);

            Assert.areSame(elSecondTr, elTr, "Expected second TR element");

            var elPenultimateTr = dt.getTbodyEl().rows[dt.getTbodyEl().rows.length-2];
            var elLastTr = dt.getTbodyEl().rows[dt.getTbodyEl().rows.length-1];
            elTr = dt.getNextTrEl(elPenultimateTr);

            Assert.areSame(elLastTr, elTr, "Expected last TR element");

            elTr = dt.getNextTrEl(elLastTr);

            Assert.areSame(null, elTr, "Expected null");
        },

        testGetPreviousTrEl: function() {
            var dt = this.createInstance();
            var elFirstTr = dt.getTbodyEl().rows[0];
            var elTr = dt.getPreviousTrEl(elFirstTr);

            Assert.areSame(null, elTr, "Expected null");

            var elSecondTr = dt.getTbodyEl().rows[1];
            elTr = dt.getPreviousTrEl(elSecondTr);

            Assert.areSame(elFirstTr, elTr, "Expected first TR element");

            var elPenultimateTr = dt.getTbodyEl().rows[dt.getTbodyEl().rows.length-2];
            var elLastTr = dt.getTbodyEl().rows[dt.getTbodyEl().rows.length-1];
            elTr = dt.getPreviousTrEl(elLastTr);

            Assert.areSame(elPenultimateTr, elTr, "Expected penultimate TR element");
        },

        testGetFirstTdEl: function() {
            var dt = this.createInstance();
            var oTestRecord = dt._oRecordSet._records[0];
            var oTestColumn = dt._oColumnSet.keys[0];
            var elCell = dt.getFirstTdEl();

            //Assert.areSame(dt.getId()+"-bdrow0-cell0", elCell.id, "Unexpected DOM ID");
            Assert.areSame(dt._elTbody.rows[0].cells[0], elCell, "Unexpected DOM element");
            DataTableAssert.areSameCell(elCell, oTestRecord, oTestColumn, dt, "Expected cell, Record, and Column to be in sync");
        },

        testGetLastTdEl: function() {
            var dt = this.createInstance();
            var nRowIndex = dt._elTbody.rows.length-1;
            var nColumnIndex = dt._elTbody.rows[nRowIndex].cells.length-1;
            var oRecordSet = dt._oRecordSet;
            var oColumnSet = dt._oColumnSet;
            var oTestRecord = dt._oRecordSet._records[oRecordSet.getLength()-1];
            var oTestColumn = dt._oColumnSet.keys[oColumnSet.keys.length-1];
            var elCell = dt.getLastTdEl();
    
            Assert.areSame(dt._elTbody.rows[nRowIndex].cells[nColumnIndex], elCell, "Unexpected DOM element");
            DataTableAssert.areSameCell(elCell, oTestRecord, oTestColumn, dt, "Expected cell, Record, and Column to be in sync");
        },

        testGetTdEl: function() {
            var dt = this.createInstance();
            var elFirstTd = dt.getTbodyEl().rows[0].cells[0];    
            var elCell = dt.getTdEl(dt.getTbodyEl().rows[0].cells[0]);

            Assert.areSame(elFirstTd, elCell, "By el ref: TD el mismatch");

            var oRecord = dt._oRecordSet._records[0];
            var oColumn = dt._oColumnSet.keys[0];
            elCell = dt.getTdEl({record:oRecord, column:oColumn});

            Assert.areSame(elFirstTd, elCell, "By obj literal: TD el mismatch");
        },

        testGetTdLinerEl: function() {
            var dt = this.createInstance();
            var elFirstTdLiner = dt.getTbodyEl().rows[0].cells[0].firstChild;
            var elLiner = dt.getTdLinerEl(dt.getTbodyEl().rows[0].cells[0]);

            Assert.areSame(elFirstTdLiner, elLiner, "By el ref: liner el mismatch");

            var oRecord = dt._oRecordSet._records[0];
            var oColumn = dt._oColumnSet.keys[0];
            elLiner = dt.getTdLinerEl({record:oRecord, column:oColumn});

            Assert.areSame(elFirstTdLiner, elLiner, "By obj literal: liner el mismatch");
        },

        testGetNextTdEl: function() {
            var dt = this.createInstance();
            var elFirstTd = dt.getFirstTdEl();
            var elSecondTd = elFirstTd.nextSibling;
            var elLastTd = dt.getLastTdEl();
            var elPenultimateTd = elLastTd.previousSibling;

            var elTd = dt.getNextTdEl(elFirstTd);

            Assert.areSame(elSecondTd, elTd, "Expected second TD element");

            elTd = dt.getNextTdEl(elPenultimateTd);

            Assert.areSame(elLastTd, elTd, "Expected last TD element");

            elTd = dt.getNextTdEl(elLastTd);

            Assert.areSame(null, elTd, "Expected null");
        },

        testGetPreviousTdEl: function() {
            var dt = this.createInstance();
            var elFirstTd = dt.getFirstTdEl();
            var elSecondTd = elFirstTd.nextSibling;
            var elLastTd = dt.getLastTdEl();
            var elPenultimateTd = elLastTd.previousSibling;

            var elTd = dt.getPreviousTdEl(elFirstTd);

            Assert.areSame(null, elTd, "Expected null");

            elTd = dt.getPreviousTdEl(elSecondTd);

            Assert.areSame(elFirstTd, elTd, "Expected first TD element");

            elTd = dt.getPreviousTdEl(elLastTd);
            
            Assert.areSame(elPenultimateTd, elTd, "Expected penultimate TD element");
        },

        testGetsAfterDeleteRow: function() {
            var dt = this.createInstance();
            var oRecord = dt.getRecord(0);
            dt.deleteRow(0);
            var elTestRow = dt.getTrEl(oRecord);

            Assert.areSame(null, elTestRow, "Expected null TR el returned");

            var nTestIndex = dt.getTrIndex(oRecord);

            Assert.areSame(null, nTestIndex, "Expected null TR index returned");

            var oTestRecord = dt.getRecord(oRecord);

            Assert.areSame(null, oTestRecord, "Expected null Record returned");

            oTestRecord = dt.getRecordSet().getRecord(oRecord);

            Assert.areSame(null, oTestRecord, "Expected null Record returned (RecordSet method)");
        }
    });
    var dtDomAccessorsTest = new DataTableTestCase(dtDomAccessorsTemplate);

    /**
     *
     *
     * Tests data load APIs.
     *
     *
     */
    var dtDataLoadTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Data Load Tests",

        testDefaults: function() {
            var dt = this.createInstance();
            Assert.areSame(true, dt.get("initialLoad"), "Expected initialLoad default value");
            Assert.areSame(null, dt.get("initialRequest"), "Expected initialRequest default value");
            Assert.areNotSame(0, dt.getRecordSet().getLength(), "Expected non-empty RecordSet");
        },

        testNoLoad: function() {
            var dt = this.createInstance(null,{initialLoad:false});
            Assert.areSame(false, dt.get("initialLoad"), "Expected initialLoad false");
            Assert.areSame(null, dt.get("initialRequest"), "Expected initialRequest default value");
            Assert.areSame(0, dt.getRecordSet().getLength(), "Expected emtpy RecordSet");
        },

        testNonInitialLoad: function() {
            var dt = this.createInstance(null,{initialLoad:false});
            Assert.areSame(0, dt.getRecordSet().getLength(), "Expected empty RecordSet to start");
            
            this.datasource.liveData = [{a:"4a",b:"4b",c:"4c"}];
            this.datasource.sendRequest(null, {
                success: dt.onDataReturnInitializeTable,
                failure: dt.onDataReturnInitializeTable,
                scope: dt
            });
            Assert.areSame(1, dt.getRecordSet().getLength(), "RecordSet should now have 1 Record");
        },
        
        testLoad: function() {
            var dt = this.createInstance(null,{initialLoad:false});
            dt.load();
            Assert.areSame(4, dt.getRecordSet().getLength(), "Expected 4 Records");

            var newDS = new YAHOO.util.LocalDataSource([[{a:"4a",b:"4b",c:"4c"}]]);
            dt.load({
                datasource:newDS,
                callback:{
                    success:dt.onDataReturnAppendRows,
                    failure:dt.onDataReturnAppendRows,
                    scope:dt
                }
            });
            Assert.areSame(5, dt.getRecordSet().getLength(), "Expected 5 Records");
        }
    });
    var dtDataLoadTest = new DataTableTestCase(dtDataLoadTemplate);

    /**
     *
     *
     * Tests row mutation APIs.
     *
     *
     */
    var dtRowMutationTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Row Mutation Tests",

        testAddRowInsert: function() {
            var dt = this.createInstance();
            dt.addRow({a:"4a",b:"4b",c:"4c"}, 0);
            
            var oTestRecord = dt._oRecordSet._records[0];
            var elRow = dt.getFirstTrEl();
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },
        
        testAddRowsInsert: function() {
            var dt = this.createInstance();
            dt.addRow([{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}], 0);
            
            var oTestRecord = dt._oRecordSet._records[0];
            var elRow = dt.getFirstTrEl();
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            oTestRecord = dt._oRecordSet._records[1];
            elRow = dt.getNextTrEl(elRow);
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            oTestRecord = dt._oRecordSet._records[2];
            elRow = dt.getNextTrEl(elRow);
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testAddRowAppend: function() {
            var dt = this.createInstance();
            dt.addRow({a:"4a",b:"4b",c:"4c"});
            
            var oTestRecord = dt._oRecordSet._records[4];
            var elRow = dt.getLastTrEl();
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last         position");
        },

        testAddRowsAppend: function() {
            var dt = this.createInstance();
            dt.addRows([{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}]);
            
            var oTestRecord = dt._oRecordSet._records[5];
            var elRow = dt.getLastTrEl();
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            oTestRecord = dt._oRecordSet._records[4];
            elRow = dt.getPreviousTrEl(elRow);
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            oTestRecord = dt._oRecordSet._records[3];
            elRow = dt.getPreviousTrEl(elRow);
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last         position");
        },

        testAddInvalidRow: function() {
            var dt = this.createInstance();
            var oData = {a:"4a", b:"4b", c:"4b"};
            dt.addRow(oData, -1);

            var oTestRecords = dt.getRecordSet().getRecords();
            for(var i=0, oTestData; i<oTestRecords.length; i++) {
                var oTestData = oTestRecords[i].getData();
                Assert.areNotSame(oData.a, oTestData.a, "Negative Record "+i+ " should not have been added");
            }

            newData = {a:"5a",b:"5b",c:"5c"};
            dt.addRow(20, newData);

            oTestRecords = dt.getRecordSet().getRecords();
            for(i=0; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(oData.a, oTestData.a, "Out of range Record "+i+" should not have been added");
            }
        },

        testAddInvalidRows: function() {
            var dt = this.createInstance();
            var aData = [{a:"4a", b:"4b", c:"4b"}];
            dt.addRows(aData, -1);

            var oTestRecords = dt.getRecordSet().getRecords();
            for(var i=0, oTestData; i<oTestRecords.length; i++) {
                var oTestData = oTestRecords[i].getData();
                Assert.areNotSame(aData[0].a, oTestData.a, "Negative Records "+i+" should not have been added");
            }

            aData = [{a:"5a", b:"5b", c:"5b"}];
            dt.addRows(20, newData);

            oTestRecords = dt.getRecordSet().getRecords();
            for(i=0; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(aData[0].a, oTestData.a, "Out of range Records "+i+" should not have updated");
            }
        },

        testUpdateRow: function() {
            var dt = this.createInstance();
            var newData = {a:"4a",b:"4b",c:"4c"};
            dt.updateRow(0, newData);
            
            var oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData.a, oData.a, "Failed to update by Record index (0)");

            newData = {a:"8a",b:"8b",c:"8c"};
            dt.updateRow(3, newData);

            oData = dt._oRecordSet._records[3].getData();
            Assert.areSame(newData.a, oData.a, "Failed to update by Record index (3)");

            newData = {a:"5a",b:"5b",c:"5c"};
            dt.updateRow(dt.getRecord(0), newData);

            oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData.a, oData.a, "Failed to update by Record instance");

            newData = {a:"6a",b:"6b",c:"6c"};
            dt.updateRow(dt.getRecord(0).getId(), newData);

            oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData.a, oData.a, "Failed to update by TR el ID");

            newData = {a:"7a",b:"7b",c:"7c"};
            dt.updateRow(Dom.get(dt.getRecord(0).getId()), newData);

            oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData.a, oData.a, "Failed to update by TR el reference");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testUpdateInvalidRow: function() {
            var dt = this.createInstance();
            var oRecord = dt.getRecord(0);
            dt.deleteRow(0);
            var oData = {a:"4a", b:"4b", c:"4b"};
            dt.updateRow(oRecord, oData);

            var oTestRecords = dt.getRecordSet().getRecords();
            for(var i=0, oTestData; i<oTestRecords.length; i++) {
                var oTestData = oTestRecords[i].getData();
                Assert.areNotSame(oData.a, oTestData.a, "Non-existent Record "+i+"should not have updated");
            }

            newData = {a:"5a",b:"5b",c:"5c"};
            dt.updateRow(-1, newData);

            oTestRecords = dt.getRecordSet().getRecords();
            for(i=0; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(oData.a, oTestData.a, "Negative Record "+i+"should not have updated");
            }

            newData = {a:"6a",b:"6b",c:"6c"};
            dt.updateRow(4, newData);

            oTestRecords = dt.getRecordSet().getRecords();
            for(i=0; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(oData.a, oTestData.a, "Out of range Record "+i+"should not have updated");
            }

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testUpdateRows: function() {
            var dt = this.createInstance();
            var newData = [{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}];
            dt.updateRows(0, newData);
            
            var oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            oData = dt._oRecordSet._records[1].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");

            newData = [{a:"6a",b:"6b",c:"6c"},{a:"7a",b:"7b",c:"7c"}];
            dt.updateRows(3, newData);

            oData = dt._oRecordSet._records[3].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            oData = dt._oRecordSet._records[4].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testUpdateInvalidRows: function() {
            var dt = this.createInstance();
            var newData = [{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}];
            dt.updateRows(-1, newData);
            
            var oTestRecords = dt.getRecordSet().getRecords();
            for(var i=0, oTestData; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(newData[0].a, oTestData.a, "Negative Record "+i+" should not have updated");
            }

            newData = [{a:"6a",b:"6b",c:"6c"},{a:"7a",b:"7b",c:"7c"}];
            dt.updateRows(4, newData);

            oTestRecords = dt.getRecordSet().getRecords();
            for(i=0; i<oTestRecords.length; i++) {
                oTestData = oTestRecords[i].getData();
                Assert.areNotSame(newData[0].a, oTestData.a, "Out of range Record "+i+" should not have updated");
            }

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testUpdateRowsRenderLoopSize: function() {
            var dt = this.createInstance(null, {renderLoopSize:2});
            var newData = [{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}];
            dt.updateRows(0, newData);
            
            var oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            oData = dt._oRecordSet._records[1].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");

            newData = [{a:"6a",b:"6b",c:"6c"},{a:"7a",b:"7b",c:"7c"}];
            dt.updateRows(3, newData);

            oData = dt._oRecordSet._records[3].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            oData = dt._oRecordSet._records[4].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");
            
            dt.on("postRenderEvent", function() {
                this.resume(function(){
                    var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
                    Assert.areSame(1, aFirstRows.length, "Expected one first row");
                    Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

                    var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
                    Assert.areSame(1, aLastRows.length, "Expected one last row");
                    Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
                });

            }, this, true);


            this.wait(1000);
        },
        
        testUpdateRowsPaginated: function() {
            var dt = this.createInstance(null, {paginator:new YAHOO.widget.Paginator({rowsPerPage:2})});
            var newData = [{a:"4a",b:"4b",c:"4c"},{a:"5a",b:"5b",c:"5c"}];
            dt.updateRows(0, newData);
            
            var oData = dt._oRecordSet._records[0].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            DataTableAssert.areSameRow(dt.getFirstTrEl(), dt._oRecordSet._records[0], dt,  "First row mismatch");
            oData = dt._oRecordSet._records[1].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");

            newData = [{a:"6a",b:"6b",c:"6c"},{a:"7a",b:"7b",c:"7c"}];
            dt.updateRows(3, newData);

            oData = dt._oRecordSet._records[3].getData();
            Assert.areSame(newData[0].a, oData.a, "Failed to update by Record index (0)");
            oData = dt._oRecordSet._records[4].getData();
            Assert.areSame(newData[1].a, oData.a, "Failed to update by Record index (0)");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testUpdateAddRow: function() {
            var dt = this.createInstance();
            dt.addRow({a:"4a",b:"4b",c:"4c"}, 0);
            dt.updateRow(0, {a:"5a",b:"5b",c:"5c"});
            dt.addRow({a:"5a",b:"5b",c:"5c"}, 0);

            var oTestRecord = dt._oRecordSet._records[0];
            var elRow = dt.getFirstTrEl();
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },


        testDeleteRow: function() {
            //TODO: Test all the arg sigs of deleteRow() method
            var dt = this.createInstance();
            dt.deleteRow(0);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(3, nRecordsLength, "Expected 3 Records left");
            Assert.areSame(3, nTrElsLength, "Expected 3 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.deleteRow(0);

            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(2, nRecordsLength, "Expected 2 Records left");
            Assert.areSame(2, nTrElsLength, "Expected 2 TR els left");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last  position");
                         
            dt.deleteRow(0);

            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(1, nRecordsLength, "Expected 1 Records left");
            Assert.areSame(1, nTrElsLength, "Expected 1 TR els left");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
            
            dt.deleteRow(0);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(0, nRecordsLength, "Expected 0 Records left");
            Assert.areSame(0, nTrElsLength, "Expected 0 TR els left");
            Assert.areSame("", dt.getMsgTbodyEl().rows[0].cells[0].style.display, "Expected message displayed");
            Assert.areSame(true, Dom.hasClass(dt.getMsgTbodyEl().rows[0].cells[0], "yui-dt-empty"), "Expected empty message");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(0, aFirstRows.length, "Expected no first row");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(0, aLastRows.length, "Expected no last row");
        },

        testDeleteRowsForward: function() {
            //TODO: Test all the arg sigs of deleteRows() method
            var dt = this.createInstance();
            dt.deleteRows(0, 2);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(2, nRecordsLength, "Expected 2 Records left");
            Assert.areSame(2, nTrElsLength, "Expected 2 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.deleteRows(0, 5);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(0, nRecordsLength, "Expected 0 Records left");
            Assert.areSame(0, nTrElsLength, "Expected 0 TR els left");
            Assert.areSame("", dt.getMsgTbodyEl().rows[0].cells[0].style.display, "Expected message displayed");
            Assert.areSame(true, Dom.hasClass(dt.getMsgTbodyEl().rows[0].cells[0], "yui-dt-empty"), "Expected empty message");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(0, aFirstRows.length, "Expected no first row");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(0, aLastRows.length, "Expected no last row");
        },

        testDeleteRowsBackward: function() {
            //TODO: Test all the arg sigs of deleteRows() method
            var dt = this.createInstance();
            dt.deleteRows(3, -2);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(2, nRecordsLength, "Expected 2 Records left");
            Assert.areSame(2, nTrElsLength, "Expected 2 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.deleteRows(1, -2);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(0, nRecordsLength, "Expected 0 Records left");
            Assert.areSame(0, nTrElsLength, "Expected 0 TR els left");
            Assert.areSame("", dt.getMsgTbodyEl().rows[0].cells[0].style.display, "Expected message displayed");
            Assert.areSame(true, Dom.hasClass(dt.getMsgTbodyEl().rows[0].cells[0], "yui-dt-empty"), "Expected empty message");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(0, aFirstRows.length, "Expected no first row");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(0, aLastRows.length, "Expected no last row");
        },

        testDeleteAppendThenInsert: function() {
            var dt = this.createInstance();
            dt.deleteRow(dt.getFirstTrEl());
            dt.addRow({a:"4a",b:"4b",c:"4c"});
            dt.addRow({a:"5a",b:"5b",c:"5c"},0);

            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testDeleteRowsInReverse: function() {
            var dt = this.createInstance();
            dt.deleteRow(3);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(3, nRecordsLength, "Expected 3 Records left");
            Assert.areSame(3, nTrElsLength, "Expected 3 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
            
            dt.deleteRow(2);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(2, nRecordsLength, "Expected 2 Records left");
            Assert.areSame(2, nTrElsLength, "Expected 2 TR els left");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
            
            dt.deleteRow(1);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(1, nRecordsLength, "Expected 1 Records left");
            Assert.areSame(1, nTrElsLength, "Expected 1 TR els left");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.deleteRow(0);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(0, nRecordsLength, "Expected 0 Records left");
            Assert.areSame(0, nTrElsLength, "Expected 0 TR els left");
            Assert.areSame("", dt.getMsgTbodyEl().rows[0].cells[0].style.display, "Expected message displayed");
            Assert.areSame(true, Dom.hasClass(dt.getMsgTbodyEl().rows[0].cells[0], "yui-dt-empty"), "Expected empty message");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(0, aFirstRows.length, "Expected no first row");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(0, aLastRows.length, "Expected no last row");
        },

        testDeleteThenInsertRows: function() {
            var dt = this.createInstance();
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.addRow({a:"4a",b:"4b",c:"4c"}, 0);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(1, nRecordsLength, "Expected 1 Record");
            Assert.areSame(1, nTrElsLength, "Expected 1 TR el");
            
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Insert 1: Row mismatch");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.addRow({a:"5a",b:"5b",c:"5c"}, 0);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(2, nRecordsLength, "Expected 2 Records");
            Assert.areSame(2, nTrElsLength, "Expected 2 TR els");
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Insert 2: Row mismatch");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");

            dt.addRow({a:"6a",b:"6b",c:"6c"}, 0);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(3, nRecordsLength, "Expected 3 Records");
            Assert.areSame(3, nTrElsLength, "Expected 3 TR els");
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt.getRecord(dt.getFirstTrEl());
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Insert 3: Row mismatch");

            aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },

        testDeleteInsertThenSortRows: function() {
            var dt = this.createInstance();
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.deleteRow(0);
            dt.addRow({a:"4a",b:"4b",c:"4c"}, 0);
            dt.addRow({a:"5a",b:"5b",c:"5c"}, 0);
            dt.addRow({a:"6a",b:"6b",c:"6c"}, 0);
            dt.sortColumn(dt.getColumn(0));

            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Expected row and Record to be in sync");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },
        
        testDeleteInvalidRow: function() {
            var dt = this.createInstance();
            dt.deleteRow(-1);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(4, nRecordsLength, "Expected 4 Records left");
            Assert.areSame(4, nTrElsLength, "Expected 4 TR els left");
        
            dt.deleteRow(4);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(4, nRecordsLength, "Expected 4 Records left");
            Assert.areSame(4, nTrElsLength, "Expected 4 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        },
        
        testDeleteInvalidRows: function() {
            var dt = this.createInstance();
            dt.deleteRows(-1, 2);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(4, nRecordsLength, "Expected 4 Records left");
            Assert.areSame(4, nTrElsLength, "Expected 4 TR els left");
        
            dt.deleteRows(-1, -2);
            
            var nRecordsLength = dt.getRecordSet().getLength();
            var nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(4, nRecordsLength, "Expected 4 Records left");
            Assert.areSame(4, nTrElsLength, "Expected 4 TR els left");

            dt.deleteRows(4, -2);
            
            nRecordsLength = dt.getRecordSet().getLength();
            nTrElsLength = dt.getTbodyEl().rows.length;
            Assert.areSame(4, nRecordsLength, "Expected 4 Records left");
            Assert.areSame(4, nTrElsLength, "Expected 4 TR els left");

            var aFirstRows = Dom.getElementsByClassName("yui-dt-first", "tr", dt.getTbodyEl());
            Assert.areSame(1, aFirstRows.length, "Expected one first row");
            Assert.areSame(0, aFirstRows[0].sectionRowIndex, "Expected row in first position");

            var aLastRows = Dom.getElementsByClassName("yui-dt-last", "tr", dt.getTbodyEl());
            Assert.areSame(1, aLastRows.length, "Expected one last row");
            Assert.areSame(dt.getTbodyEl().rows.length-1, aLastRows[0].sectionRowIndex, "Expected row in last position");
        }
    });
    var dtRowMutationTest = new DataTableTestCase(dtRowMutationTemplate);

    /**
     *
     *
     * Tests APIsfrom within formatters
     *
     *
     */
    var dtFormatterTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Formatter Tests",
        
        testCellFormatter: function() {
            this.createInstance(null, null, [{key:"a"},{key:"b", formatter:function(el, record, column){
                var tr = this.getTrEl(el);
                Assert.areSame("tr", tr.nodeName.toLowerCase(), "Expected tr element");
                
                var td = this.getFirstTdEl(tr);
                Assert.areSame(0, this.getCellIndex(td), "Expected td from first column");

                td = this.getNextTdEl(td);
                Assert.areSame(1, this.getCellIndex(td), "Expected td from second column");

                td = this.getLastTdEl(tr);
                Assert.areSame(2, this.getCellIndex(td), "Expected td from last column");

                td = this.getPreviousTdEl(td);
                Assert.areSame(1, this.getCellIndex(td), "Expected td from last column");
                
                var tr = this.getTrEl(el);
                Assert.areSame("tr", tr.nodeName.toLowerCase(), "Expected tr");
                Assert.areSame(true, YAHOO.util.Dom.isAncestor(tr, el), "Expected tr to be parent");
                }
            },{key:"c"}]);
        },

        testRowFormatter: function() {
            this.createInstance(null, {formatter:function(elTr, oRecord) {
                var td = this.getFirstTdEl(elTr);
                Assert.areSame(0, this.getCellIndex(td), "Expected td from first column");

                td = this.getNextTdEl(elTr);
                Assert.areSame(1, this.getCellIndex(td), "Expected td from second column");

                td = this.getLastTdEl(elTr);
                Assert.areSame(3, this.getCellIndex(td), "Expected td from last column");

                td = this.getPreviousTdEl(elTr);
                Assert.areSame(2, this.getCellIndex(td), "Expected td from last column");
            }
        });
        }
    });
    var dtFormatterTest = new DataTableTestCase(dtFormatterTemplate);

    /**
     *
     *
     * Tests sorting APIs.
     *
     *
     */
    var dtSortingTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Sorting Tests",
        
       dsData: [
            {a:0,b:"zero"},
            {a:1,b:"one"},
            {a:2,b:"two"},
            {a:3,b:"three"}
        ],
            
        dsConfig: {
            responseType:YAHOO.util.DataSource.TYPE_JSARRAY,
            responseSchema:{fields:["a","b"]}
        },

        columns: [
            {key:"a",label:"numbers",sortable:true},
            {key:"b",label:"strings",sortable:true}
        ],

        testSortByElementClick: function() {
            var dt = this.createInstance();
            var oColumn = dt.getColumn(1);
            var el = dt.getThEl(oColumn);
            UserAction.click(el);
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            
            Assert.areSame(oTestRecord.getId(), elRow.id, "Click TH: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Click TH: Row mismatch");

            var childEl = el.firstChild;
            UserAction.click(childEl);
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            
            Assert.areSame("zero", dt.getRecord(dt.getFirstTrEl()).getData("b"), "Click container: Unexpected data");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Click container: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Click container: Row mismatch");
            
            UserAction.click(childEl);
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            
            Assert.areSame("one", dt.getRecord(dt.getFirstTrEl()).getData("b"), "Click label: Unexpected data");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Click label: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Click label: Row mismatch");
        },

        testSortNumber: function() {
            var dt = this.createInstance();
            var oColumn = dt.getColumn(0);
            dt.sortColumn(oColumn);
            
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top asc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom asc: Row mismatch");

            dt.sortColumn(oColumn);
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top desc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom desc: Row mismatch");
        },

        testSortString: function() {
            var dt = this.createInstance();
            var oColumn = dt.getColumn(1);
            dt.sortColumn(oColumn);

            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top asc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom asc: Row mismatch");

            dt.sortColumn(oColumn);
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top desc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom desc: Row mismatch");
        },

        testSortCustomPassesField: function() {
          var dt = this.createInstance(); 
          var oColumn = dt.getColumn(1);
          oColumn.sortOptions = { 
            field: "ahasuerus",
            sortFunction: function(a, b, desc, field) {
              Assert.areSame("ahasuerus", field, "field is supposed to be passed to a custom sort routine!");
              return 0;
            }
          }
          dt.sortColumn(oColumn);


        },

        testInsertThenSort: function() {
            var dt = this.createInstance();
            dt.addRow({a:4,b:"four"}, 0)
            dt.sortColumn(dt.getColumn(1));
            
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top asc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom asc: Row mismatch");
            
            dt.sortColumn(dt.getColumn(1));

            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top desc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom desc: Row mismatch");
        },
        
        testSortThenAppend: function() {
            var dt = this.createInstance();
            dt.sortColumn(dt.getColumn(1));
            dt.addRow({a:4,b:"four"});

            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top asc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom asc: Row mismatch");

            dt.sortColumn(dt.getColumn(1));
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Top asc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Top desc: Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Bottom desc: Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Bottom desc: Row mismatch");
        },
        
        testDeleteAppendInsertThenSort: function() {
            var dt = this.createInstance();
            dt.deleteRow(dt.getFirstTrEl());
            dt.addRow({a:4,b:"four"});
            dt.addRow({a:5,b:"five"},0);
            dt.sortColumn(dt.getColumn(1));

            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");
        }
    });
    var dtSortingTest = new DataTableTestCase(dtSortingTemplate);
    
    /**
     *
     *
     * Tests row selection APIs.
     *
     *
     */
    var dtRowSelectionTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Row Selection Tests",

        testSelectUnselectByEl: function() {
            var dt = this.createInstance();
            var elRow = dt.getFirstTrEl();
            dt.selectRow(elRow);
            
            var aSelectedRows = dt.getSelectedRows();
            Assert.areSame(true, YAHOO.util.Dom.hasClass(elRow, "yui-dt-selected"), "Failed to apply CSS");
            ArrayAssert.contains(elRow.id, aSelectedRows, "Failed to select first row");
            
            dt.unselectRow(elRow);
            
            aSelectedRows = dt.getSelectedRows();
            Assert.areSame(false, YAHOO.util.Dom.hasClass(elRow, "yui-dt-selected"), "Failed to remove CSS");
            ArrayAssert.doesNotContain(elRow.id, aSelectedRows, "Failed to unselect first row");
        },
        
        testSelectThenInsert: function() {
            var dt = this.createInstance();
            var elRow = dt.getFirstTrEl();
            dt.selectRow(elRow);
            dt.addRow({a:"4a",b:"4b",c:"4c"}, 0);
            
            var aSelectedRows = dt.getSelectedRows();
            var elTestRow = dt.getTbodyEl().rows[1];
            Assert.areSame(true, YAHOO.util.Dom.hasClass(elTestRow, "yui-dt-selected"), "Failed to apply CSS");
            ArrayAssert.contains(elTestRow.id, aSelectedRows, "Second row should be selected");

            elTestRow = dt.getTbodyEl().rows[0];
            Assert.areNotSame(true, YAHOO.util.Dom.hasClass(elTestRow, "yui-dt-selected"), "Unexpected CSS");
            ArrayAssert.doesNotContain(elTestRow.id, aSelectedRows, "First row should not be selected");
        },
        
        testSingleSelect: function() {
            var oConfig = {
                selectionMode:"single"
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = Dom.get(dt.getRecord(0).getId());
            UserAction.click(el);
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var elTestRow = dt.getTbodyEl().rows[0];
            ArrayAssert.itemsAreSame([elTestRow.id], aSelectedRows, "Expected only first row selected");
            ArrayAssert.itemsAreSame([el], aSelectedEls, "Expected only first TR el selected");

            el = Dom.get(dt.getRecord(1).getId());
            UserAction.click(el);
            el = Dom.get(dt.getRecord(2).getId());
            UserAction.click(el);
            el = Dom.get(dt.getRecord(3).getId());
            UserAction.click(el);
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var elTestRow = dt.getTbodyEl().rows[3];
            ArrayAssert.itemsAreSame([elTestRow.id], aSelectedRows, "Expected only fourth row selected");
            ArrayAssert.itemsAreSame([el], aSelectedEls, "Expected only fourth TR el selected");
        },
        
        testShiftSelect: function() {
            var dt = this.createInstance();
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = Dom.get(dt.getRecord(0).getId());
            UserAction.click(el);
            el = Dom.get(dt.getRecord(3).getId());
            UserAction.click(el, {"shiftKey":true});
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecordIds = [
                dt._oRecordSet._records[0].getId(),
                dt._oRecordSet._records[1].getId(),
                dt._oRecordSet._records[2].getId(),
                dt._oRecordSet._records[3].getId()
            ];
            var aTestTrEls = [
                dt.getTbodyEl().rows[0],
                dt.getTbodyEl().rows[1],
                dt.getTbodyEl().rows[2],
                dt.getTbodyEl().rows[3]
            ];
            ArrayAssert.itemsAreSame(aTestRecordIds, aSelectedRows, "Expected four rows selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected four TR els selected");

            el = Dom.get(dt.getRecord(2).getId());
            UserAction.click(el);
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            aTestRecordIds = [
                dt._oRecordSet._records[2].getId()
            ];
            aTestTrEls = [
                dt.getTbodyEl().rows[2]
            ];
            ArrayAssert.itemsAreSame(aTestRecordIds, aSelectedRows, "Expected only third row selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected only third TR el selected");

            el = Dom.get(dt.getRecord(0).getId());
            UserAction.click(el, {"shiftKey":true});
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            aTestRecordIds = [
                dt._oRecordSet._records[2].getId(),
                dt._oRecordSet._records[1].getId(),
                dt._oRecordSet._records[0].getId()
            ];
            aTestTrEls = [
                dt.getTbodyEl().rows[0],
                dt.getTbodyEl().rows[1],
                dt.getTbodyEl().rows[2]
            ];
            ArrayAssert.itemsAreSame(aTestRecordIds, aSelectedRows, "Expected three rows selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected three TR els selected");
        },
        
        testUnselectAllRows: function() {
            var dt = this.createInstance();
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = Dom.get(dt.getRecord(0).getId());
            UserAction.click(el);
            el = Dom.get(dt.getRecord(3).getId());
            UserAction.click(el, {"shiftKey":true});
            dt.unselectAllRows();

            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecordIds = [];
            var aTestTrEls = [];
            ArrayAssert.itemsAreSame(aTestRecordIds, aSelectedRows, "Shift-select: Expected no rows selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Shift-select: Expected no TR els selected");

            el = Dom.get(dt.getRecord(1).getId());
            UserAction.click(el);
            el = Dom.get(dt.getRecord(3).getId());
            UserAction.click(el, {"ctrlKey":true});
            dt.unselectAllRows();

            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            ArrayAssert.itemsAreSame(aTestRecordIds, aSelectedRows, "Ctrl-select: Expected no rows selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Ctrl-select: Expected no rows selected");
        },
        
        testAppendX3ThenDeleteSingleSelectedFromTop: function() {
            var i;
            var dt = this.createInstance(null, {selectionMode:"single"});
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            dt.addRow({a:"4a",b:"4b",c:"4c"});
            dt.addRow({a:"5a",b:"5b",c:"5c"});
            var el = Dom.get(dt.getRecord(0).getId());
            UserAction.click(el);
            var rows = dt.getSelectedRows();
            for(i=0; i<rows.length; i++) {
                var row = rows[i];
                //TODO: Support passing in Record ID to dt.getRecordIndex and rs.getRecordIndex
                dt.deleteRow(dt.getRecordIndex(dt.getRecord(row)));
            }
            
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(5, dt.getRecordSet().getLength(), "Expected 5 Records");
            Assert.areSame(5, dt.getTbodyEl().rows.length, "Expected 5 TR els");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");

            el = Dom.get(dt.getRecord(1).getId());
            UserAction.click(el);
            rows = dt.getSelectedRows();
            for(i=0; i<rows.length; i++) {
                var row = rows[i];
                //TODO: Support passing in Record ID to dt.getRecordIndex and rs.getRecordIndex
                dt.deleteRow(dt.getRecordIndex(dt.getRecord(row)));
            }
            
            elRow = dt.getFirstTrEl();
            oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(4, dt.getRecordSet().getLength(), "Expected 4 Records");
            Assert.areSame(4, dt.getTbodyEl().rows.length, "Expected 4 TR els");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");
        },

        testIsSelected: function() {
            var dt = this.createInstance();
            var elRow = dt.getFirstTrEl();
            
            // TR el reference agnst row selection
            dt.selectRow(elRow);
            Assert.areSame(true, dt.isSelected(elRow), "Failed to detect selected row by TR");
            dt.unselectRow(elRow);
            Assert.areSame(false, dt.isSelected(elRow), "Failed to detect unselected row by TR");
            
            // TD el reference agnst row selection
            dt.selectRow(elRow.cells[0]);
            Assert.areSame(true, dt.isSelected(elRow.cells[0]), "Failed to detect selected row by TD");
            dt.unselectRow(elRow.cells[0]);
            Assert.areSame(false, dt.isSelected(elRow.cells[0]), "Failed to detect unselected by row TD");

            // Child elements agnst row selection
            dt.selectRow(elRow.cells[0].firstChild);
            Assert.areSame(true, dt.isSelected(elRow.cells[0].firstChild), "Failed to detect selected row by liner el");
            dt.unselectRow(elRow.cells[0].firstChild);
            Assert.areSame(false, dt.isSelected(elRow.cells[0].firstChild), "Failed to detect unselected by row liner el");
            
            // TD el reference agnst cell selection
            dt.selectCell(elRow.cells[0]);
            Assert.areSame(true, dt.isSelected(elRow.cells[0]), "Failed to detect selected cell by TD");
            dt.unselectCell(elRow.cells[0]);
            Assert.areSame(false, dt.isSelected(elRow.cells[0]), "Failed to detect unselected cell by TD");

            // Child elements agnst cell selection
            dt.selectCell(elRow.cells[0].firstChild);
            Assert.areSame(true, dt.isSelected(elRow.cells[0].firstChild), "Failed to detect selected cell by liner el");
            dt.unselectCell(elRow.cells[0].firstChild);
            Assert.areSame(false, dt.isSelected(elRow.cells[0].firstChild), "Failed to detect unselected cell by liner el");

            // Record instance
            dt.selectRow(dt._oRecordSet._records[0]);
            Assert.areSame(true, dt.isSelected(dt._oRecordSet._records[0]), "Failed to detect selected row by Record instance");
            dt.unselectRow(dt._oRecordSet._records[0]);
            Assert.areSame(false, dt.isSelected(dt._oRecordSet._records[0]), "Failed to detect unselected row by Record instance");
            
            // RecordSet index
            dt.selectRow(0);
            Assert.areSame(true, dt.isSelected(0), "Failed to detect selected row by RecordSet index");
            dt.unselectRow(0);
            Assert.areSame(false, dt.isSelected(0), "Failed to detect unselected row by RecordSet index");
            
            // {record: oRecord, column: oColumn}
            dt.selectCell({record:dt._oRecordSet._records[0], column: dt._oColumnSet.keys[0]});
            Assert.areSame(true, dt.isSelected({record:dt._oRecordSet._records[0], column: dt._oColumnSet.keys[0]}), "Failed to detect selected cell by object literal");
            dt.unselectCell({record:dt._oRecordSet._records[0], column: dt._oColumnSet.keys[0]});
            Assert.areSame(false, dt.isSelected({record:dt._oRecordSet._records[0], column: dt._oColumnSet.keys[0]}), "Failed to detect unselected cell by object literal");
        }
    });
    var dtRowSelectionTest = new DataTableTestCase(dtRowSelectionTemplate);

    /**
     *
     *
     * Tests cell selection APIs.
     *
     *
     */
    var dtCellSelectionTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Row Selection Tests"
    });
    var dtCellSelectionTest = new DataTableTestCase(dtCellSelectionTemplate);
    
    /**
     *
     *
     * Tests pagination APIs.
     *
     *
     */
    var dtPaginationTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Pagination Tests",
        
       dsData: [
            {a:0},{a:1},{a:2},{a:3},{a:4},
            {a:5},{a:6},{a:7},{a:8},{a:9},
            {a:10},{a:11},{a:12},{a:13},{a:14},
            {a:15},{a:16},{a:17},{a:18},{a:19},
            {a:20},{a:21}
        ],

        dsConfig: {
            responseType:YAHOO.util.DataSource.TYPE_JSARRAY,
            responseSchema:{fields:["a"]}
        },

        columns: [{key:"a", sortable:true}],

        testPagination: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5})
            }
            var dt = this.createInstance(null, oConfig);
                Assert.areSame(5, dt.get("paginator").getTotalPages(), "Expected 5 pages");
        },

        testPageThenSort: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                sortedBy: {key:"a"}
            }
            var dt = this.createInstance(null, oConfig);
            dt.get("paginator").setPage(2);
            dt.sortColumn(dt.getColumn("a"));
            
            var elRow = dt.getFirstTrEl();
            var oTestRecord = dt._oRecordSet._records[0];
            Assert.areSame(1, dt.get("paginator").getCurrentPage(), "Expected to be on page 1");
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");

            elRow = dt.getLastTrEl();
            oTestRecord = dt._oRecordSet._records[dt.getTbodyEl().rows.length-1];
            Assert.areSame(oTestRecord.getId(), elRow.id, "Unexpected DOM ID");
            DataTableAssert.areSameRow(elRow, oTestRecord, dt, "Row mismatch");
        },

        testDeleteRowsCurrentPage: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5})
            }
            var dt = this.createInstance(null, oConfig);
            dt.deleteRows(0,2);
            
            Assert.areSame(4, dt.get("paginator").getTotalPages(), "Expected 4 pages");
        },
        
         testDeleteRowsDifferentPage: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5})
            }
            var dt = this.createInstance(null, oConfig);
            dt.get("paginator").setPage(5);
            dt.deleteRows(0,2);
            
            Assert.areSame(4, dt.get("paginator").getTotalPages(), "Expected 4 pages");
            Assert.areSame(4, dt.get("paginator").getCurrentPage(), "Expected to be on page 4");
        },
        
        testSingleSelectAcrossPages: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                selectionMode:"single"
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = dt.getTrEl(0); // record 0
            UserAction.click(el);
            dt.get("paginator").setPage(4);
            el = dt.getTrEl(0); // record 15
            UserAction.click(el);
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecords = [
                dt._oRecordSet._records[15].getId()
            ];
            var aTestTrEls = [
                dt.getTbodyEl().rows[0]
            ];

            dt.get("paginator").setPage(1);
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            Assert.areSame(1, aSelectedRows.length, "Expected one Record to still be selected");
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected last record to still be selected");
            Assert.areSame(0, aSelectedEls.length, "Expected no TR els selected");
        },
        
        testSingleSelectAcrossPagesAfterSort: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                selectionMode:"single",
                sortedBy: {key:"a"}
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            dt.get("paginator").setPage(4);
            var el = dt.getTrEl(0); // record 15
            UserAction.click(el);
            dt.sortColumn(dt.getColumn(0));
            el = dt.getTrEl(0); // record 0
            UserAction.click(el);
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecords = [
                dt._oRecordSet._records[0].getId()
            ];
            var aTestTrEls = [
                dt.getTbodyEl().rows[0]
            ];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected only last row of first page (Record index 4) selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected only last TR el selected");
            
            dt.get("paginator").setPage(3);
            el = dt.getTrEl(2); // record 12
            UserAction.click(el);
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            aTestRecords = [
                dt._oRecordSet._records[12].getId()
            ];
            aTestTrEls = [
                dt.getTbodyEl().rows[2]
            ];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected only third row of third page (Record index 12) selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected only third TR el selected");
        },

        testShiftSelectAcrossPages: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                selectionMode:"standard"
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = dt.getTrEl(0); // record 0
            UserAction.click(el);
            dt.get("paginator").setPage(2);
            el = dt.getTrEl(1); //record 6
            UserAction.click(el, {"shiftKey":true});
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecords = [
                dt._oRecordSet._records[0].getId(),
                dt._oRecordSet._records[1].getId(),
                dt._oRecordSet._records[2].getId(),
                dt._oRecordSet._records[3].getId(),
                dt._oRecordSet._records[4].getId(),
                dt._oRecordSet._records[5].getId(),
                dt._oRecordSet._records[6].getId()
            ];
            var aTestTrEls = [
                dt.getTbodyEl().rows[0],
                dt.getTbodyEl().rows[1]
            ];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected seven rows across two pages selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected first two TR els selected");

            el = dt.getTrEl(2); // record 7
            UserAction.click(el);
            
            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            aTestRecords = [
                dt._oRecordSet._records[7].getId()
            ];
            aTestTrEls = [
                dt.getTbodyEl().rows[2]
            ];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected only third row of second page selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected only third TR el selected");
        },

        testUnselectAllRowsAcrossPages: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                selectionMode:"standard"
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = dt.getTrEl(0); // record 0
            UserAction.click(el);
            dt.get("paginator").setPage(2);
            el = dt.getTrEl(1); // record 6
            UserAction.click(el, {"shiftKey":true});
            dt.unselectAllRows();

            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecords = [];
            var aTestTrEls = [];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Shift-select: Expected no rows on any page selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Shift-select: Expected no TR els selected");

            el = dt.getTrEl(2); // record 7
            UserAction.click(el);
            el = dt.getTrEl(4); // record 9
            UserAction.click(el, {"ctrlKey":true});
            dt.get("paginator").setPage(3);
            el = dt.getTrEl(3); // record 13
            UserAction.click(el, {"ctrlKey":true});
            el = dt.getTrEl(4); // record 14
            UserAction.click(el, {"ctrlKey":true});
            dt.unselectAllRows();

            aSelectedRows = dt.getSelectedRows();
            aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Ctrl-select: Expected no rows on any page selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Ctrl-select: Expected no TR els selected");
        },


        testSingleSelectAcrossRowsPerPage: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:5}),
                selectionMode:"single"
            }
            var dt = this.createInstance(null, oConfig);
            dt.subscribe("rowClickEvent",dt.onEventSelectRow,dt);
            var el = dt.getTrEl(0); // record 0
            UserAction.click(el);
            dt.get("paginator").setPage(2);
            el = dt.getTrEl(2); // record 7
            UserAction.click(el);
            dt.get("paginator").setRowsPerPage(10);
            dt.get("paginator").setPage(1);
            //dt.get("paginator").setState({page:1,rowsPerPage:10});
            //dt.render();
            
            var aSelectedRows = dt.getSelectedRows();
            var aSelectedEls = Dom.getElementsByClassName("yui-dt-selected", "*", dt.getTbodyEl());
            var aTestRecords = [
                dt._oRecordSet._records[7].getId()
            ];
            var aTestTrEls = [
                dt.getTbodyEl().rows[7]
            ];
            ArrayAssert.itemsAreSame(aTestRecords, aSelectedRows, "Expected only one row selected");
            ArrayAssert.itemsAreSame(aTestTrEls, aSelectedEls, "Expected seventh TR el selected");
        },
        
        testGetRecordAcrossPages: function() {
            var oConfig = {
                paginator: new YAHOO.widget.Paginator({rowsPerPage:2}),
                selectionMode:"single"
            }
            var dt = this.createInstance(null, oConfig);
            dt.get("paginator").setPage(2);

            // Get Record on this page
            var rs = dt.getRecordSet();
            var oRecord = rs._records[3];
            var sRecordId = oRecord.getId();

            var el = dt.getTbodyEl().rows[1];
            var oTestRecord = dt.getRecord(el);
            Assert.areSame(oRecord, oTestRecord, "Expected to get last Record by el reference");

            oTestRecord = dt.getRecord(3);
            Assert.areSame(oRecord, oTestRecord, "Expected to get last Record by position index");

            oTestRecord = dt.getRecord(sRecordId);
            Assert.areSame(oRecord, oTestRecord, "Expected to get last Record by Record ID");

            // Get Record on a different page
            oRecord = rs._records[1];
            sRecordId = oRecord.getId();
            oTestRecord = dt.getRecord(el);
            Assert.areNotSame(oRecord, oTestRecord, "Last Record on page 1 should NOT match last Record on this page");

            oTestRecord = dt.getRecord(1);
            Assert.areSame(oRecord, oTestRecord, "Expected to get Record 0 by position index");
        }
    });
    var dtPaginationTest = new DataTableTestCase(dtPaginationTemplate);
    
    /**
     *
     *
     * Tests the _cloneObject API.
     *
     *
     */
    var dtCloneObjectTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Clone Object Tests",
        
        testUndefined: function() {
            var dt = this.createInstance();
            Assert.areSame(undefined, YAHOO.widget.DataTable._cloneObject(undefined), "Expected undefined");
        },

        testNull: function() {
            var dt = this.createInstance();
            Assert.areSame(null, YAHOO.widget.DataTable._cloneObject(null), "Expected null");
        },
        
        testNaN: function() {
            var dt = this.createInstance();
            Assert.areSame(isNaN(NaN), isNaN(YAHOO.widget.DataTable._cloneObject(NaN)), "Expected NaN");
        },
        
        testZero: function() {
            var dt = this.createInstance();
            Assert.areSame(0, YAHOO.widget.DataTable._cloneObject(0), "Expected 0");
        },
        
        testNumber: function() {
            var dt = this.createInstance();
            Assert.areSame(5, YAHOO.widget.DataTable._cloneObject(5), "Expected 5");
        },
        
        testString: function() {
            var dt = this.createInstance();
            Assert.areSame("abc", YAHOO.widget.DataTable._cloneObject("abc"), "Expected abc");
        },
        
        testFalse: function() {
            var dt = this.createInstance();
            Assert.areSame(false, YAHOO.widget.DataTable._cloneObject(false), "Expected false");
        },
        
        testTrue: function() {
            var dt = this.createInstance();
            Assert.areSame(true, YAHOO.widget.DataTable._cloneObject(true), "Expected true");
        },
        
        testArraySimple: function() {
            var dt = this.createInstance();
            ArrayAssert.itemsAreSame([1,2,3], YAHOO.widget.DataTable._cloneObject([1,2,3]), "Expected simple array");
        },
        
        testObjectSimple: function() {
            var dt = this.createInstance();
            ObjectAssert.propertiesAreEqual({"a":1,"b":2,"c":3}, YAHOO.widget.DataTable._cloneObject({"a":1,"b":2,"c":3}), "Expected simple object");
        }
    });

    var dtCloneObjectTest = new DataTableTestCase(dtCloneObjectTemplate);

    /**
     *
     *
     * Tests the APIs when there are non-primary rows.
     *
     *
     */
    var dtRowExpansionTemplate = YAHOO.lang.merge(dtBaseTemplate, {
        name: "DataTable Row Expansion Tests",
        
        testGetTdEl: function() {
            var dt = this.createInstance(),
                allRows = dt.getTbodyEl().rows;

            // Element, ID, Record/Column
            var td = dt.getTdEl(allRows[0].childNodes[0].firstChild);
            Assert.areSame("0a", td.firstChild.innerHTML, "Element unexpanded: Expected first row primary cell still");

            td.id="test0";
            td = dt.getTdEl("test0");
            Assert.areSame("0a", td.firstChild.innerHTML, "ID unexpanded: Expected first row primary cell still");

            td = dt.getTdEl({record:dt.getRecord(0), column:dt.getColumn(0)});
            Assert.areSame("0a", td.firstChild.innerHTML, "Record/Column unexpanded: Expected first row primary cell still");

            td = dt.getTdEl(allRows[1].childNodes[0].firstChild);
            Assert.areSame("1a", td.firstChild.innerHTML, "Element unexpanded: Expected second row primary cell");

            td.id="test1";
            td = dt.getTdEl("test1");
            Assert.areSame("1a", td.firstChild.innerHTML, "ID unexpanded: Expected second row primary cell");

            td = dt.getTdEl({record:dt.getRecord(1), column:dt.getColumn(0)});
            Assert.areSame("1a", td.firstChild.innerHTML, "Record/Column unexpanded: Expected second row primary cell");

            td = dt.getTdEl(allRows[3].childNodes[2].firstChild);
            Assert.areSame("3c", td.firstChild.innerHTML, "Element unexpanded: Expected fourth row primary cell");

            td.id="test2";
            td = dt.getTdEl("test2");
            Assert.areSame("3c", td.firstChild.innerHTML, "ID unexpanded: Expected fourth row primary cell");

            td = dt.getTdEl({record:dt.getRecord(3), column:dt.getColumn(0)});
            Assert.areSame("3a", td.firstChild.innerHTML, "Record/Column unexpanded: Expected fourth row primary cell");

            td = dt.getTdEl("foo");
            Assert.areSame(null, td, "ID unexpanded: Expected null");

            this.expandRows(dt);

            // Element, ID, Record/Column
            td = dt.getTdEl(allRows[0].childNodes[0].firstChild);
            Assert.areSame("0a", td.firstChild.innerHTML, "Element: Expected first row primary cell still");

            td.id="test0";
            td = dt.getTdEl("test0");
            Assert.areSame("0a", td.firstChild.innerHTML, "ID: Expected first row primary cell still");

            td = dt.getTdEl({record:dt.getRecord(0), column:dt.getColumn(0)});
            Assert.areSame("0a", td.firstChild.innerHTML, "Record/Column: Expected first row primary cell still");

            td = dt.getTdEl(allRows[2].childNodes[0].firstChild);
            Assert.areSame("1a", td.firstChild.innerHTML, "Element: Expected second row primary cell");

            td.id="test1";
            td = dt.getTdEl("test1");
            Assert.areSame("1a", td.firstChild.innerHTML, "ID: Expected second row primary cell");

            td = dt.getTdEl({record:dt.getRecord(1), column:dt.getColumn(0)});
            Assert.areSame("1a", td.firstChild.innerHTML, "Record/Column: Expected second row primary cell");

            td = dt.getTdEl(allRows[6].childNodes[0].firstChild);
            Assert.areSame("3a", td.firstChild.innerHTML, "Element: Expected fourth row primary cell");

            td.id="test2";
            td = dt.getTdEl("test2");
            Assert.areSame("3a", td.firstChild.innerHTML, "ID: Expected fourth row primary cell");

            td = dt.getTdEl({record:dt.getRecord(3), column:dt.getColumn(0)});
            Assert.areSame("3a", td.firstChild.innerHTML, "Record/Column: Expected fourth row primary cell");

            td = dt.getTdEl("foo");
            Assert.areSame(null, td, "ID: Expected null");
        },

        testGetTrEl: function() {
            var dt = this.createInstance(),
                allRows = dt.getTbodyEl().rows;

            // Element, ID, Page index, Record
            var tr = dt.getTrEl(allRows[0].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element unexpanded: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Element unexpanded: Expected first row primary cell");

            tr = dt.getTrEl(allRows[0].id);
            Assert.areSame(3, tr.childNodes.length, "ID unexpanded: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "ID unexpanded: Expected first row primary cell");

            tr = dt.getTrEl(0);
            Assert.areSame(3, tr.childNodes.length, "Pageindex unexpanded: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Pageindex unexpanded: Expected first row primary cell");

            tr = dt.getTrEl(dt.getRecord(0));
            Assert.areSame(3, tr.childNodes.length, "Record unexpanded: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Record unexpanded: Expected first row primary cell");

            tr = dt.getTrEl(allRows[1].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element unexpanded: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Element unexpanded: Expected second row primary cell");

            tr = dt.getTrEl(allRows[1].id);
            Assert.areSame(3, tr.childNodes.length, "ID unexpanded: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "ID unexpanded: Expected second row primary cell");

            tr = dt.getTrEl(1);
            Assert.areSame(3, tr.childNodes.length, "Pageindex unexpanded: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Pageindex unexpanded: Expected second row primary cell");

            tr = dt.getTrEl(dt.getRecord(1));
            Assert.areSame(3, tr.childNodes.length, "Record unexpanded: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Record unexpanded: Expected second row primary cell");

            tr = dt.getTrEl(allRows[3].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element unexpanded: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Element unexpanded: Expected fourth row primary cell");

            tr = dt.getTrEl(allRows[3].id);
            Assert.areSame(3, tr.childNodes.length, "ID unexpanded: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "ID unexpanded: Expected fourth row primary cell");

            tr = dt.getTrEl(3);
            Assert.areSame(3, tr.childNodes.length, "Pageindex unexpanded: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Pageindex unexpanded: Expected fourth row primary cell");

            tr = dt.getTrEl(dt.getRecord(3));
            Assert.areSame(3, tr.childNodes.length, "Record unexpanded: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Record unexpanded: Expected fourth row primary cell");

            tr = dt.getTrEl(allRows[4]);
            Assert.areSame(null, tr, "Element unexpanded: Expected null");

            tr = dt.getTrEl("foo");
            Assert.areSame(null, tr, "ID unexpanded: Expected null");

            tr = dt.getTrEl(4);
            Assert.areSame(null, tr, "Pageindex unexpanded: Expected null");

            this.expandRows(dt);

            // Element, ID, Page index, Record
            tr = dt.getTrEl(allRows[0].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Expected first row primary cell");

            tr = dt.getTrEl(allRows[0].id);
            Assert.areSame(3, tr.childNodes.length, "ID: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Expected first row primary cell");

            tr = dt.getTrEl(0);
            Assert.areSame(3, tr.childNodes.length, "Pageindex: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Expected first row primary cell");

            tr = dt.getTrEl(dt.getRecord(0));
            Assert.areSame(3, tr.childNodes.length, "Record: Expected first row primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Expected first row primary cell");

            tr = dt.getTrEl(allRows[2].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Expected second row primary cell");

            tr = dt.getTrEl(allRows[2].id);
            Assert.areSame(3, tr.childNodes.length, "ID: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Expected second row primary cell");

            tr = dt.getTrEl(1);
            Assert.areSame(3, tr.childNodes.length, "Pageindex: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Expected second row primary cell");

            tr = dt.getTrEl(dt.getRecord(1));
            Assert.areSame(3, tr.childNodes.length, "Record: Expected second row primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Expected second row primary cell");

            tr = dt.getTrEl(allRows[6].childNodes[0]);
            Assert.areSame(3, tr.childNodes.length, "Element: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Expected fourth row primary cell");

            tr = dt.getTrEl(allRows[6].id);
            Assert.areSame(3, tr.childNodes.length, "ID: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Expected fourth row primary cell");

            tr = dt.getTrEl(3);
            Assert.areSame(3, tr.childNodes.length, "Pageindex: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Expected fourth row primary cell");

            tr = dt.getTrEl(dt.getRecord(3));
            Assert.areSame(3, tr.childNodes.length, "Record: Expected fourth row primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Expected fourth row primary cell");

            tr = dt.getTrEl(allRows[8]);
            Assert.areSame(null, tr, "Element: Expected null");

            tr = dt.getTrEl("foo");
            Assert.areSame(null, tr, "ID: Expected null");

            tr = dt.getTrEl(7);
            Assert.areSame(null, tr, "Pageindex: Expected null");
        },

        testGetFirstTrEl: function() {
            var dt = this.createInstance();
            var tr = dt.getFirstTrEl();
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Expected primary cell");

            this.expandRows(dt);

            tr = dt.getFirstTrEl();
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("0a", tr.childNodes[0].firstChild.innerHTML, "Expected primary cell");
        },
        
        testGetLastTrEl: function() {
            var dt = this.createInstance();
            var tr = dt.getLastTrEl();
            Assert.areSame(3, tr.childNodes.length, "Unexpected: Expected primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Unexpected: Expected primary cell");

            this.expandRows(dt);

            tr = dt.getLastTrEl();
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("3a", tr.childNodes[0].firstChild.innerHTML, "Expected primary cell");
        },
        
        testGetPreviousTrEl: function() {
            var dt = this.createInstance();

            // Element, ID, pageindex, Record
            var tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Element unexpanded: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr.id);
            Assert.areSame(null, tr, "ID unexpanded: Expected null");

            tr = 0;
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Pageindex unexpanded: Expected null");

            tr = dt.getRecord(0);
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Record unexpanded: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Element unexpanded: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr.id, true);
            Assert.areSame(null, tr, "ID unexpanded: Expected null");

            tr = 0;
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Pageindex unexpanded: Expected null");

            tr = dt.getRecord(0);
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Record unexpanded: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Element unexpanded: Expected third row");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Element unexpanded: Expected third row cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr.id);
            Assert.areSame(3, tr.childNodes.length, "ID unexpanded: Expected third row");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "ID unexpanded: Expected third row cell");

            tr = 3;
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Pageindex unexpanded: Expected second row");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Pageindex unexpanded: Expected third row cell");

            tr = dt.getRecord(3);
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Record unexpanded: Expected third row");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Record unexpanded: Expected third row cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Element unexpanded: Expected third row");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Element unexpanded: Expected third row  cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr.id, true);
            Assert.areSame(3, tr.childNodes.length, "ID unexpanded: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "ID unexpanded: Expected primary cell");

            tr = 3;
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Pageindex unexpanded: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Pageindex unexpanded: Expected primary cell");

            tr = dt.getRecord(3);
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Record unexpanded: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Record unexpanded: Expected primary cell");

            this.expandRows(dt);

            // Element, ID, pageindex, Record
            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Element: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr.id);
            Assert.areSame(null, tr, "ID: Expected null");
            
            tr = 0;
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Pageindex: Expected null");

            tr = dt.getRecord(0);
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(null, tr, "Record: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Element: Expected null");

            tr = dt.getFirstTrEl();
            tr = dt.getPreviousTrEl(tr.id, true);
            Assert.areSame(null, tr, "ID: Expected null");

            tr = 0;
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Pageindex: Expected null");

            tr = dt.getRecord(0);
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(null, tr, "Record: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Element: Expected expansion");
            Assert.areSame("expando 2", tr.childNodes[0].innerHTML, "Element: Expected expansion cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr.id);
            Assert.areSame(1, tr.childNodes.length, "ID: Expected expansion");
            Assert.areSame("expando 2", tr.childNodes[0].innerHTML, "ID: Expected expansion cell");

            tr = 3;
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Pageindex: Expected expansion");
            Assert.areSame("expando 2", tr.childNodes[0].innerHTML, "Pageindex: Expected expansion cell");

            tr = dt.getRecord(3);
            tr = dt.getPreviousTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Record: Expected expansion");
            Assert.areSame("expando 2", tr.childNodes[0].innerHTML, "Record: Expected expansion cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Element: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Element: Expected primary cell");

            tr = dt.getLastTrEl();
            tr = dt.getPreviousTrEl(tr.id, true);
            Assert.areSame(3, tr.childNodes.length, "ID: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "ID: Expected primary cell");

            tr = 3;
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Pageindex: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Pageindex: Expected primary cell");

            tr = dt.getRecord(3);
            tr = dt.getPreviousTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Record: Expected primary");
            Assert.areSame("2a", tr.childNodes[0].firstChild.innerHTML, "Record: Expected primary cell");
        },

        testGetNextTrEl: function() {
            var dt = this.createInstance();

            // Element, ID, page index, Record
            var tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Element: Expected second row cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr.id);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: ID: Expected second row cell");

            tr = 0;
            tr = dt.getNextTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Pageindex: Expected second row cell");

            tr = dt.getRecord(0);
            tr = dt.getNextTrEl(tr);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Record: Expected second row cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Element: Expected second row cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr.id, true);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected second row");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: ID: Expected second row cell");

            tr = 0;
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Pageindex: Expected second row cell");

            tr = dt.getRecord(0);
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Unexpanded: Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Unexpanded: Record: Expected second row cell");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr);
            Assert.areSame(null, tr, "Unexpanded: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr.id);
            Assert.areSame(null, tr, "Unexpanded: Expected null");

            tr = 3;
            tr = dt.getNextTrEl(tr);
            Assert.areSame(null, tr, "Unexpanded: Expected null");

            tr = dt.getRecord(3);
            tr = dt.getNextTrEl(tr);
            Assert.areSame(null, tr, "Unexpanded: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Unexpanded: Element: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr.id, true);
            Assert.areSame(null, tr, "Unexpanded: ID: Expected null");

            tr = 3;
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Unexpanded: Pageindex: Expected null");

            tr = dt.getRecord(3);
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Unexpanded: Record: Expected null");

            this.expandRows(dt);

            // Element, ID, page index, Record
            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 0", tr.childNodes[0].innerHTML, "Element: Expected expansion cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr.id);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 0", tr.childNodes[0].innerHTML, "ID: Expected expansion cell");

            tr = 0;
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 0", tr.childNodes[0].innerHTML, "Pageindex: Expected expansion cell");

            tr = dt.getRecord(0);
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 0", tr.childNodes[0].innerHTML, "Record: Expected expansion cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Element: Expected primary cell");

            tr = dt.getFirstTrEl();
            tr = dt.getNextTrEl(tr.id, true);
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "ID: Expected primary cell");

            tr = 0;
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Pageindex: Expected primary cell");

            tr = dt.getRecord(0);
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(3, tr.childNodes.length, "Expected primary");
            Assert.areSame("1a", tr.childNodes[0].firstChild.innerHTML, "Record: Expected primary cell");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 3", tr.childNodes[0].innerHTML, "Element: Expected expansion cell");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr.id);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 3", tr.childNodes[0].innerHTML, "ID: Expected expansion cell");

            tr = 3;
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 3", tr.childNodes[0].innerHTML, "Pageindex: Expected expansion cell");

            tr = dt.getRecord(3);
            tr = dt.getNextTrEl(tr);
            Assert.areSame(1, tr.childNodes.length, "Expected expansion");
            Assert.areSame("expando 3", tr.childNodes[0].innerHTML, "Record: Expected expansion cell");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Element: Expected null");

            tr = dt.getLastTrEl();
            tr = dt.getNextTrEl(tr.id, true);
            Assert.areSame(null, tr, "ID: Expected null");

            tr = 3;
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Pageindex: Expected null");

            tr = dt.getRecord(3);
            tr = dt.getNextTrEl(tr, true);
            Assert.areSame(null, tr, "Record: Expected null");
        },

        testGetFirstTdEl: function() {
            var dt = this.createInstance();
            
            var td = dt.getFirstTdEl();
            Assert.areSame("0a", td.firstChild.innerHTML, "Expected primary cell");

            td = dt.getFirstTdEl(3);
            Assert.areSame("3a", td.firstChild.innerHTML, "Expected relative cell, primary");

            td = dt.getFirstTdEl(4);
            Assert.areSame(null, td, "Expected null");

            this.expandRows(dt);

            td = dt.getFirstTdEl();
            Assert.areSame("0a", td.firstChild.innerHTML, "Expected primary cell");

            td = dt.getFirstTdEl(3);
            Assert.areSame("3a", td.firstChild.innerHTML, "Expected relative cell, primary");

            td = dt.getFirstTdEl(4);
            Assert.areSame(null, td, "Expected null");
        },

        testGetLastTdEl: function() {
            var dt = this.createInstance();
            
            var td = dt.getLastTdEl();
            Assert.areSame("3c", td.firstChild.innerHTML, "Expected last primary cell");

            td = dt.getLastTdEl(0);
            Assert.areSame("0c", td.firstChild.innerHTML, "Expected first relative cell, primary");

            td = dt.getLastTdEl(1);
            Assert.areSame("1c", td.firstChild.innerHTML, "Expected first relative cell, expansion");

            td = dt.getLastTdEl(4);
            Assert.areSame(null, td, "Expected null");

            this.expandRows(dt);

            td = dt.getLastTdEl();
            Assert.areSame("3c", td.firstChild.innerHTML, "Expected last primary cell");
            
            td = dt.getLastTdEl(0);
            Assert.areSame("0c", td.firstChild.innerHTML, "Expected first relative cell, primary");

            td = dt.getLastTdEl(1);
            Assert.areSame("1c", td.firstChild.innerHTML, "Expected first relative cell, expansion");

            td = dt.getLastTdEl(4);
            Assert.areSame(null, td, "Expected null");
        },

        testGetAboveTdEl: function() {
            var dt = this.createInstance();

            // Element, ID, {Record, Column}
            var td = dt.getFirstTdEl();
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Unexpanded: Element: Expected null");

            td = dt.getFirstTdEl();
            td.id = "test0";
            td = dt.getAboveTdEl(td.id);
            Assert.areSame(null, td, "Unexpanded: ID: Expected null");

            td = {record:dt.getRecord(0), column:dt.getColumn(0)};
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Unexpanded: Record/Column: Expected null");

            td = dt.getLastTdEl();
            td = dt.getAboveTdEl(td);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: Element: Expected cell");

            td = dt.getLastTdEl();
            td.id = "test1";
            td = dt.getAboveTdEl(td.id);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: ID: Expected cell");

            td = {record:dt.getRecord(3), column:dt.getColumn(2)};
            td = dt.getAboveTdEl(td);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: Record/Column: Expected cell");

            td = dt.getLastTdEl();
            td = dt.getAboveTdEl(td, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: Element: Expected above primary cell");

            td = dt.getLastTdEl();
            td.id = "test2";
            td = dt.getAboveTdEl(td.id, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: ID: Expected above primary cell");

            td = {record:dt.getRecord(3), column:dt.getColumn(2)};
            td = dt.getAboveTdEl(td, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "Unexpanded: Record/Column: Expected above primary cell");

            this.expandRows(dt);

            // Element, ID, {Record, Column}
            td = dt.getFirstTdEl();
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Element: Expected null");
            
            td = dt.getFirstTdEl();
            td.id = "test0";
            td = dt.getAboveTdEl(td.id);
            Assert.areSame(null, td, "ID: Expected null");
            
            td = {record:dt.getRecord(0), column:dt.getColumn(0)};
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Record/Column: Expected null");

            td = dt.getLastTdEl();
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Element: Expected no corresponding cell");

            td = dt.getLastTdEl();
            td.id = "test1";
            td = dt.getAboveTdEl(td.id);
            Assert.areSame(null, td, "ID: Expected no corresponding cell");

            td = {record:dt.getRecord(3), column:dt.getColumn(2)};
            td = dt.getAboveTdEl(td);
            Assert.areSame(null, td, "Record/Column: Expected no corresponding cell");

            td = dt.getLastTdEl();
            td = dt.getAboveTdEl(td, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "Element: Expected above primary cell");

            td = dt.getLastTdEl();
            td.id = "test2";
            td = dt.getAboveTdEl(td.id, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "ID: Expected above primary cell");

            td = {record:dt.getRecord(3), column:dt.getColumn(2)};
            td = dt.getAboveTdEl(td, true);
            Assert.areSame("2c", td.firstChild.innerHTML, "Record/Column: Expected above primary cell");
        },

        testGetBelowTdEl: function() {
            var dt = this.createInstance();

            // Element, ID, {Record, Column}
            var td = dt.getLastTdEl();
            td = dt.getBelowTdEl(td);
            Assert.areSame(null, td, "Unexpanded Element: Expected null");

            td = dt.getLastTdEl();
            td.id = "test0";
            td = dt.getBelowTdEl(td.id);
            Assert.areSame(null, td, "Unexpanded ID: Expected null");

            td = {record: dt.getRecord(3), column: dt.getColumn(2)};
            td = dt.getBelowTdEl(td);
            Assert.areSame(null, td, "Unexpanded Record/Column: Expected null");

            td = dt.getFirstTdEl();
            td = dt.getBelowTdEl(td);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded Element: Expected cell");

            td = dt.getFirstTdEl();
            td.id="test1";
            td = dt.getBelowTdEl(td.id);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded ID: Expected cell");

            td = {record: dt.getRecord(0), column: dt.getColumn(0)};
            td = dt.getBelowTdEl(td);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded Record/Column: Expected cell");

            td = dt.getFirstTdEl();
            td = dt.getBelowTdEl(td, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded Element: Expected below primary cell");

            td = dt.getFirstTdEl();
            td.id = "test2";
            td = dt.getBelowTdEl(td.id, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded ID: Expected below primary cell");

            td = {record: dt.getRecord(0), column: dt.getColumn(0)};
            td = dt.getBelowTdEl(td, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "Unexpanded Record/Column: Expected below primary cell");

            this.expandRows(dt);

            // Element, ID, {Record, Column}
            td = dt.getLastTdEl();
            td = dt.getBelowTdEl(td);
            Assert.areSame(null, td, "Element: Expected null");

            td = dt.getLastTdEl();
            td.id = "test0";
            td = dt.getBelowTdEl(td.id);
            Assert.areSame(null, td, "ID: Expected null");

            td = {record: dt.getRecord(3), column: dt.getColumn(2)};
            td = dt.getBelowTdEl(td);
            Assert.areSame(null, td, "Record/Column: Expected null");

            td = dt.getFirstTdEl();
            td = dt.getBelowTdEl(td);
            Assert.areSame("expando 0", td.innerHTML, "Element: Expected expansion cell");

            td = dt.getFirstTdEl();
            td.id="test1";
            td = dt.getBelowTdEl(td.id);
            Assert.areSame("expando 0", td.innerHTML, "ID: Expected expansion cell");

            td = {record: dt.getRecord(0), column: dt.getColumn(0)};
            td = dt.getBelowTdEl(td);
            Assert.areSame("expando 0", td.innerHTML, "Record/Column: Expected expansion cell");

            td = dt.getFirstTdEl();
            td = dt.getBelowTdEl(td, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "Element: Expected below primary cell");

            td = dt.getFirstTdEl();
            td.id = "test2";
            td = dt.getBelowTdEl(td.id, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "ID: Expected below primary cell");

            td = {record: dt.getRecord(0), column: dt.getColumn(0)};
            td = dt.getBelowTdEl(td, true);
            Assert.areSame("1a", td.firstChild.innerHTML, "Record/Column: Expected below primary cell");
        },

        testGetTrIndex: function() {
            var dt = this.createInstance(),
                tbody = dt.getTbodyEl();

            // Element, ID, Record, Record index
            var index = dt.getTrIndex(tbody.rows[0]);
            Assert.areSame(0, index, "Unexpanded: Element: Expected first primary row");

            index = dt.getTrIndex(tbody.rows[1]);
            Assert.areSame(1, index, "Unexpanded: Element: Expected second row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-2]);
            Assert.areSame(2, index, "Unexpanded: Element: Expected third row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-1]);
            Assert.areSame(3, index, "Unexpanded: Element: Expected last row");

            index = dt.getTrIndex(tbody.rows[0].id);
            Assert.areSame(0, index, "Unexpanded: ID: Expected first primary row");

            index = dt.getTrIndex(tbody.rows[1].id);
            Assert.areSame(1, index, "Unexpanded: ID: Expected second row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-2].id);
            Assert.areSame(2, index, "Unexpanded: ID: Expected second row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-1].id);
            Assert.areSame(3, index, "Unexpanded: ID: Expected last row");

            index = dt.getTrIndex(dt.getRecord(0));
            Assert.areSame(0, index, "Unexpanded: Record: Expected first primary row");

            index = dt.getTrIndex(dt.getRecord(1));
            Assert.areSame(1, index, "Unexpanded: Record: Expected second primary row");

            index = dt.getTrIndex(dt.getRecord(3));
            Assert.areSame(3, index, "Unexpanded: Record: Expected last primary row");

            index = dt.getTrIndex(0);
            Assert.areSame(0, index, "Unexpanded: Record index: Expected first primary row");

            index = dt.getTrIndex(1);
            Assert.areSame(1, index, "Unexpanded: Record index: Expected second primary row");

            index = dt.getTrIndex(3);
            Assert.areSame(3, index, "Unexpanded: Record index: Expected last primary row");

            this.expandRows(dt);

            // Element, ID, Record, Record index
            index = dt.getTrIndex(tbody.rows[0]);
            Assert.areSame(0, index, "Element: Expected first primary row");

            index = dt.getTrIndex(tbody.rows[1]);
            Assert.areSame(null, index, "Element: Not a primary row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-2]);
            Assert.areSame(6, index, "Element: Expected last primary row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-1]);
            Assert.areSame(null, index, "Element: Not a primary row");
            
            index = dt.getTrIndex(tbody.rows[0].id);
            Assert.areSame(0, index, "ID: Expected first primary row");

            index = dt.getTrIndex(tbody.rows[1].id);
            Assert.areSame(null, index, "ID: Not a primary row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-2].id);
            Assert.areSame(6, index, "ID: Expected last primary row");

            index = dt.getTrIndex(tbody.rows[tbody.rows.length-1].id);
            Assert.areSame(null, index, "ID: Not a primary row");

            index = dt.getTrIndex(dt.getRecord(0));
            Assert.areSame(0, index, "Record: Expected first primary row");

            index = dt.getTrIndex(dt.getRecord(1));
            Assert.areSame(2, index, "Record: Expected second primary row");

            index = dt.getTrIndex(dt.getRecord(3));
            Assert.areSame(6, index, "Record: Expected last primary row");

            index = dt.getTrIndex(0);
            Assert.areSame(0, index, "Record index: Expected first primary row");

            index = dt.getTrIndex(1);
            Assert.areSame(2, index, "Record index: Expected second primary row");

            index = dt.getTrIndex(3);
            Assert.areSame(6, index, "Record index: Expected last primary row");
        }

    });

    var dtRowExpansionTest = new DataTableTestCase(dtRowExpansionTemplate);

    /**
     *
     *
     * Runs tests.
     *
     *
     */
    YAHOO.util.Event.addListener(window, "load", function() {
        var datatablesuite = new TestSuite("DataTable Test Suite");
        datatablesuite.add(dtConstructionTest);
        datatablesuite.add(dtDomAccessorsTest);
        datatablesuite.add(dtDataLoadTest);
        datatablesuite.add(dtRowMutationTest);
        datatablesuite.add(dtFormatterTest);
        datatablesuite.add(dtSortingTest);
        datatablesuite.add(dtRowSelectionTest);
        datatablesuite.add(dtCellSelectionTest);
        datatablesuite.add(dtPaginationTest);
        datatablesuite.add(dtCloneObjectTest);
        datatablesuite.add(dtRowExpansionTest);

        
        TestRunner.add(datatablesuite);
    });
})();
