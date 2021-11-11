il.Util.addOnLoad(
    function () {

        var AC_DATASOURCE = 'ilias.php?baseClass=ilSearchController&cmd=autoComplete';

        // we must bind the blur event before the autocomplete item is added
        document.getElementById("main_menu_search").addEventListener(
            "blur",
            (e) => {e.stopImmediatePropagation();}
        );

        $("#main_menu_search").autocomplete({
            source: AC_DATASOURCE + "&search_type=4",
            appendTo: "#mm_search_menu_ac",
            open: function (event, ui) {
                $(".ui-autocomplete").position({
                    my: "left top",
                    at: "left top",
                    of: $("#mm_search_menu_ac")
                })
            },
            minLength: 3
        });

        $("#ilMMSearchMenu input[type='radio']").change(function () {
            $("#main_menu_search").focus();

            /* close current search */
            $("#main_menu_search").autocomplete("close");

            /* append search type */

            var orig_datasource = AC_DATASOURCE;
            var type_val = $('input[name=root_id]:checked', '#mm_search_form').val();

            $("#main_menu_search").autocomplete("option",
                {
                    source: orig_datasource + "&search_type=" + type_val
                });

            /* start new search */
            $("#main_menu_search").autocomplete("search");
        });
    }
);