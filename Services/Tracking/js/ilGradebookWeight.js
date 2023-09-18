/**
 * Created by lucas.sencabaugh on 13/04/2016.
 */
/**
 * Created by lucas.sencabaugh on 30/11/2015.
 */

$(function() {

    var url = "/Services/Tracking/classes/gradebook/class.ilLPGradebookCTRL.php";
    $( document ).ready(function()
    {
        var params={};
        window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,
            function(str,key,value) {
                    params[key] = value;
        });

        var classList = '';
        var $set = $('#gradebookContainer ul');
        var len = $set.length;


        //Getting Array of class list to make multiple 'mini' sortables so each sortable area cannot merge in others
        $( "#gradebookContainer ul" ).each(function(index, element) {
            if($(this).attr("id")) {
                if (index == len - 1) {
                    classList += '#'+$(this).attr("id");
                } else {
                    classList += '#'+$(this).attr("id")+',';
                }
            }
        });

        $(classList).sortable({
            cursor: "grabbing",
            opacity: 0.5,
            placeholder: "ui-state-highlight",
            update: function (event, ui) {
                var data = {};
                data.parent = $(this).attr('id').replace('sortable_','');
                data.order = $(this).sortable('serialize');
            }
        });

        $('.toggleButton').bootstrapToggle();

        //Keeps Gradebook in line with height of Sortable List
        $('#sortable_1').css("min-height", $('#gradebookContainer').height());


        //Taking inputs out of the validation process on toggle button click
        $('.toggleButton').on('change', function() {
            var weight = $(this).closest('.row-color').find('.weight').eq(0);
            if (!$(this).prop('checked')) {
                weight.prop('disabled', true);
                weight.removeClass('weight-required weight-invalid weight-valid weight-enabled');
                weight.css('color','transparent');


                var ul = $(this).closest('li').find('> ul');

                //Turning off all children
                if(ul)
                {
                    var children = ul.find('> li');
                    children.each(function() {
                        $(this).find('.toggleButton').bootstrapToggle('off')
                    });
                }

                //weight.val('');
                //console.log('a');
            } else {
                //console.log('b');
                //console.log(weight);
                weight.prop('disabled', false);
                weight.css('color','black');

                var ul = $(this).closest('li').find('> ul');

                //Turning on all children
                if(ul)
                {
                    var children = ul.find('> li');
                    children.each(function() {
                        $(this).find('.toggleButton').bootstrapToggle('on')
                    });
                }

                //If there is a parent and it's not on turn it on!
                if($(this).parents("li").eq(1) && !$(this).parents("li").eq(1).find('.toggleButton').prop('checked'))
                {
                    $(this).parents("li").eq(1).find('.toggleButton').bootstrapToggle('on')
                }


                //Turing on all parents


                var attr = $(this).closest('li').attr('data-grp-color');
                if (typeof attr !== typeof undefined && attr !== false)
                {
                    //console.log('b-1');
                    groupColorCheck($(this).closest('ul'),$(this).closest('li').attr('data-grp-color'));
                    weight.addClass('weight-required weight-enabled');
                }
                else
                {
                    //console.log('b-2');
                    weight.removeClass('weight-required weight-invalid weight-valid');
                    weight.addClass('weight-required weight-enabled');
                }
            }
        });

        //Checking validation of inputs on load initially
        var items = {};

        $('input.weight').each(function() {
            items[$(this).attr('data-parent-id')] = true;
        });

        var gradeSections = new Array();
        for(var i in items) {
            gradeSections.push(i);
        }

        $.each( gradeSections, function() {
            gradeCheck(this);
        });

        //Checking if anything is enabled on this Gradebook
        if ( !$("input.weight:not([disabled])").length ) {
            $('.alert-info').html('You have no objects enabled for this grade book.')
            $('.alert-info').show().focus();
        }

        $('#sortable_1').show();

        //Checking if inputs are valid on change
        $( "input.weight" ).on( "change", function() {
            var parentId = $(this).attr('data-parent-id');
            gradeCheck(parentId);
        });

        $(document).on( 'keyup', '.grpLeader', function () {
            var grpLeaderValue = $(this).val();
            var color = $(this).closest('li').attr('data-grp-color');
            var parent = $(this).attr('data-parent-id');

            //console.log('keyup');

            $("input[data-parent-id="+parent+"].weight-enabled").each(function()
            {
                if($(this).closest('li').attr('data-grp-color') == color)
                {
                    $(this).val(grpLeaderValue);
                }
            });
        });

        function gradeCheck(parentId)
        {
            var flag = true;
            var total = 0;
            var color;
            var val = 0;
            var unique = [];

            $("input[data-parent-id="+parentId+"].weight-enabled").each(function()
            {
                color = $(this).closest('li').attr('data-grp-color');
                if(color)
                {
                    if(unique.indexOf(color) === -1)
                    {
                        unique.push(color);
                        total +=  parseInt($(this).val(), 10);
                        $(this).addClass('grpLeader').prop("disabled", false);
                        val = $(this).val();
                    }
                    else
                    {
                        $(this).prop("disabled", true).prop("disabled", true).css('color','rgba(0, 0, 0, 0.44)');
                        $(this).val(val);
                    }
                }
                else
                {
                    total +=  parseInt($(this).val(), 10);
                }
            });

            if(total !== 100) {
                $("input[data-parent-id="+parentId+"].weight-enabled").each(function(){
                    if(parseInt($(this).val(), 10) == null) {
                        $(this).removeClass('weight-valid weight-invalid');
                        $(this).addClass('weight-required')
                        //console.log('c');
                        $(this).val(0);
                    } else {
                        $(this).removeClass('weight-valid weight-invalid');
                        $(this).addClass('weight-invalid')
                        flag = false;
                    }
                });
            } else {
                $("input[data-parent-id="+parentId+"].weight-enabled").each(function(){
                    $(this).removeClass('weight-valid weight-invalid weight-enabled weight-required');
                    $(this).addClass('weight-valid weight-enabled');
                    if(parseInt($(this).val(), 10) == null) {
                        $(this).val(0);
                    }
                });
                flag = true;
            }
            return flag;
        }

        function groupColorCheck(parent,color)
        {
            var groups = parent.find('> li');
            var count = 0;
            var groupLeaderValue;

            $(groups).each(function()
            {
                var li = $(this);
                var attr = $(this).attr('data-grp-color');


                //console.log(li);

                // For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
                if (typeof attr !== typeof undefined && attr !== false) {

                    var toggle = li.first('div.toggle').eq(0);


                    if(li.attr("data-grp-color") == color)
                    {

                        count++;

                        if(count > 1)
                        {
                            if(!toggle.hasClass( "off" ))
                            {
                                $(this).find("input[name=weight]").eq(0).prop("disabled", true).css('color','rgba(0, 0, 0, 0.44)');
                                $(this).find("input[name=weight]").eq(0).val(groupLeaderValue);
                            }
                        }
                        else
                        {
                            groupLeaderValue =  $(this).find("input[name=weight]").val();
                            $(this).find("input[name=weight]").eq(0).prop("disabled", false).css('color','black');
                        }
                    }
                }
            });

        }



        //This code chunk is for the iteration of colors
        var colors = [
            { "color": "#A93226", "id": "0" },
            { "color": "#E74C3C", "id": "1" },
            { "color": "#F39C12", "id": "2" },
            { "color": "#F7DC6F", "id": "3" },
            { "color": "#82E0AA", "id": "4" },
            { "color": "#1E8449", "id": "5" },
            { "color": "#AED6F1", "id": "6" },
            { "color": "#2874A6", "id": "7" },
            { "color": "#154360", "id": "8" },
            { "color": "#A569BD", "id": "9" },
            { "color": "#000000", "id": "10"}
        ];
        var counter;

        // the next line, of course, assumes you have an element with id="next"
        $('span.color-picker').click(function () {
            counter = $(this).closest('li').attr('data-grp-color');

            if(counter == 10)
            {
             counter = 0;
            }
            else
            {
                counter++;
            }
            // the modulus (%) operator resets the counter to 0
            // when it reaches the length of the array
            $(this).closest('.listObject').css('border-color', colors[counter].color);
            $(this).closest('.color-picker').css('color', colors[counter].color);
            $(this).closest('li').attr('data-grp-color', counter);

            //If Toggle isn't on don't bother checking
            if (!$(this).closest('li').find('.toggle').hasClass( "off" ))
            {
                groupColorCheck($(this).closest('ul'),counter);
            }
        });


        // Saving the Weights
        $('#save').click(function (event ) {
            event.preventDefault();
            $('.alert').hide();
            var flag = true;

            $.each( gradeSections, function() {
                var result = gradeCheck(this);
                if(result == false){
                    flag = false;
                }
            });

            var passing_grade = $('#passing_grade').val();

            //if passing grade was not sent in as a number.
            if(isNaN(passing_grade)){
                $('.alert-danger').html('Passing Grade is not a number');
                $('.alert-danger').show().focus();
                return;
             }

             //if passing grade is greater than 100 or less than 0.
             if(passing_grade > 100 || passing_grade < 0 ) {
                $('.alert-danger').html('Passing Grade must be between 0 - 100 %');
                $('.alert-danger').show().focus();
                return;
             }


            if(flag == false) {
                $('.alert-danger').html('Issue with weight');
                $('.alert-danger').show().focus();
            } else {
                var out = [];
                function processOneLi(node) {
                    var aNode = node.find("a:first");
                    var iNode = node.find("input.weight");
                    var weightCheck = node.find("input.weight-enabled").length;
                    var retVal = {
                        "obj_id": node.attr('data-obj-id'),
                        "name": aNode.text().trim(),
                        "weight": ((weightCheck) ? iNode.val() : ''),
                        "depth": node.parentsUntil('#sortable_1','ul').length,
                        "color": ((node.attr('data-grp-color')) ? node.attr('data-grp-color') : ''),
                    };

                    node.find("> ul > li").each(function() {
                        if (!retVal.hasOwnProperty("children")) {
                            retVal.children = [];
                        }
                        retVal.children.push(processOneLi($(this)));
                    });
                    return retVal;
                }

                $("#sortable_1").children("li").each(function() {
                    out.push(processOneLi($(this)));
                });

                var passing_grade = $( "#passing_grade" ).val();

                var formData = {
                    'action'  : 'saveGradebookWeight',
                    'nodes'  : out,
                    'ref_id': params['ref_id'],
                    'passing_grade': passing_grade
                };

                $.ajax({
                    type: "POST",
                    data: formData,
                    url: url,
                    beforeSend: function() { $('.loader').show(); $('#save').prop('disabled', true); },
                    complete: function() { $('.loader').hide(); $('#save').prop('disabled', false); },
                    success: function(data) {

                        data = JSON.parse(data);

                        if(!$("#gradebook_select option[value="+data.data.revision_id+"]").length > 0)
                        {
                            if(data.data.revision_id){
                                $('#gradebook_select').prepend($('<option>', {
                                    value: data.data.revision_id,
                                    text: 'Gradebook Revision: '+data.data.revision_id+' - '+
                                    data.data.revision_creator+' - '+data.data.create_date
                                }));
                                $('#gradebook_select').val(data.data.revision_id);
                            }

                        }

                        $('.alert-success').html('Successfully Saved')
                        $('.alert-success').show().focus();

                    },
                    error:function(jqXHR, textStatus, errorThrown){

                        $('.alert-danger').html(errorThrown);
                        $('.alert-danger').show().focus();
                    }
                });

            }
        });
    });
});

