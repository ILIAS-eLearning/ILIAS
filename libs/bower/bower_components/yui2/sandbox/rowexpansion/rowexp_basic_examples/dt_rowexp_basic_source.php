
<div id="expandable_table"></div>

<script src="<?php echo($assetsDirectory); ?>js/rowexpansion.js"></script>
<script type="text/javascript">
/* Modify as needed */
YAHOO.util.Event.onDOMReady( function() {
        YAHOO.example.Basic = function() {

                /**
                *
                * Create a YUI DataSource instance. This will create an XHR datasource and will use YQL 
                * to query the Flickr web service.
                *
                **/
                var myDataSource = new YAHOO.util.DataSource('<?php echo $assetsDirectory; ?>php/yql_proxy.php?q=select%20*%20from%20flickr.photos.interestingness(20)&format=json');
                        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
                        myDataSource.connXhrMode = "queueRequests";
                        myDataSource.responseSchema = {
                            resultsList: "query.results.photo"
                        };

                /**
                *
                * Create a YUI DataTable instance.
                *
                **/
                var myDataTable = new YAHOO.widget.RowExpansionDataTable(
                        "expandable_table",
                        [
                            {
                                label:"",
                                /**
                                * This formatter adds a class that will be used to style a 
                                * trigger in the first column
                                **/
                                formatter:function( el, oRecord, oColumn, oData ) {

                                    YAHOO.util.Dom.addClass( el.parentNode, "yui-dt-expandablerow-trigger" );

                                }
                            },
                            {
                                key:"title",
                                label:"Interestingness",
                                width : '200px',
                                /**
                                * This formatter includes a default string in cells where the record
                                * is missing a title. This just makes the finsished product look nicer.
                                **/
                                formatter: function( el, oRecord, oColumn, oData ){
                                    
                                    el.innerHTML = oData || '--[ No description ]--';
                                    
                                }
                            }
                        ],
                        myDataSource,
                            { 
                                /**
                                * The "rowExpansionTemplate" property is passed a string. This is passed 
                                * through YAHOO.lang.substitute which can match tokens (represented with brackets), 
                                * which contain keys from the DataTables data.
                                **/
                                rowExpansionTemplate : '<img src="http://farm{farm}.static.flickr.com/{server}/{id}_{secret}_m_d.jpg" /><div><a href="http://flickr.com/photos/{owner}">See more photos from this Flickr User</a></div>' 
                            }
                        );

                /**
                *
                * Subscribe to the "cellClickEvent" which will yui-dt-expandablerow-trigger the expansion 
                * when the user clicks on the yui-dt-expandablerow-trigger column
                *
                **/
                myDataTable.subscribe( 'cellClickEvent', myDataTable.onEventToggleRowExpansion );
                
                return {
                        oDS: myDataSource,
                        oDT: myDataTable
                };
        }();
});
</script>
