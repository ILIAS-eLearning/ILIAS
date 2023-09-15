/**
 * Created by lucas.sencabaugh on 13/04/2016.
 */
/**
 * Created by lucas.sencabaugh on 30/11/2015.
 */

$(function() {

    var url = "/Services/Tracking/classes/gradebook/class.ilLPGradebookCTRL.php";
    var overall_data = [];

    $( document ).ready(function()
    {
        var params={};
        window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,
            function(str,key,value) {
                params[key] = value;
            });

        var firstUser = $("#gradebook-users").find("option:first").attr("selected", true).val();
        getUserData(firstUser);
        $("#next, #prev").click(function() {
            id = $("#gradebook-users :selected")[this.id]().val();
            $("#gradebook-users :selected")[this.id]().prop("selected", true);
            if(id)
            {
                getUserData(id);
            }
        });

        $("#gradebook-users").change(function() {
            id = $("#gradebook-users :selected").val();
            if(id)
            {
                getUserData(id);
            }
        });

        function getUserData(id)
        {
            $('#grade-table tbody').empty();
            var formData = {
                'action'  : 'getGradesForUser',
                'usr_id'  : id,
                'ref_id':params['ref_id']
            };

            $.ajax({
                type: "POST",
                data: formData,
                url: url,
                success: function(response) {

                    response = JSON.parse(response);

                    //console.log(response);

                    if(response.status === 'success')
                    {

                        overall_data = response.data.overall_data[0];
                        //console.log(overall_data);
                        $("#revision").val(overall_data['revision']);

                        var tbody ='';

                        $(response.data.object_data).each(function()
                        {

                            tbody += '<tr data-obj-id="'+this.obj_id+'" data-obj-lptype="'+this.lp_type+'">';
                            if(this.is_gradeable == 0){
                                tbody += '<td><span title="Group grade is determined by children" class="obj-learning-progress glyphicon glyphicon-lock" aria-hidden="true"></span></td>';
                            }
                            else{
                                tbody += '<td>';
                                if(parseInt(this.lp_type) === 0){
                                    tbody+= '<span  title="Automated learning progress" class="obj-learning-progress glyphicon glyphicon-ok" aria-hidden="true"></span>';
                                } else {
                                    tbody+='<span  title="Learning progress is either disabled or not available" class="obj-learning-progress glyphicon glyphicon-pencil" aria-hidden="true"></span>';
                                }
                                tbody+='</td>';
                            }
                            console.table(this);
                            tbody += '<td>'+((this.placement_depth) ? this.placement_depth: '')+'</td>';
                            tbody += '<td>'+((this.weight) ? this.weight : '')+'</td>';
                            tbody += '<td>'+ ( this.lp_type == 0 || this.is_gradeable == 0 ? ((this.actual) ? this.actual : '') : '<input class="actual" type="text" value="'+((this.actual) ? this.actual : '')+'">')+  '</td>';
                            tbody += '<td>'+((this.adjusted) ? this.adjusted : '')+'</td>';

                            tbody += '<td>';
                            tbody += '<img src="'+this.img+'"/>';
                            tbody += '<select '+( this.lp_type == 0 ? 'disabled' : '' )+' class="obj-status form-control pull-right">';
                            tbody += "<option value='0' "+(this.status == 0 ? "selected='selected'" : '')+">Not Attempted</option>";
                            tbody += "<option value='1' "+(this.status == 1 ? "selected='selected'" : '')+">In Progress</option>";
                            tbody += "<option value='2' "+(this.status == 2 ? "selected='selected'" : '')+">Passed</option>";
                            tbody += "<option value='3' "+(this.status == 3 ? "selected='selected'" : '')+">Failed</option>";
                            tbody += '</select></td>';

                            tbody += '<td><img alt="'+this.type_Alt+'" title="'+this.type_Alt+'" src="./templates/default/images/icon_'+this.type+'.svg" class="ilListItemIcon"></td>';
                            tbody += '<td><a target="_blank" href="'+this.url+'">'+this.title+'</a></td>';
                            tbody += '<td>'+((this.graded_on) ? this.graded_on : '')+'</td>';
                            tbody += '<td>'+((this.graded_by && this.is_gradeable) ? this.graded_by : '')+'</td>';
                            tbody += '</tr>';
                        });
                        $('#grade-table tbody').html(tbody);

                        var imgAlt = '';

                        switch (overall_data['status']) {
                            case 0:
                                imgAlt = 'Not Attempted';
                                break;
                            case '1':
                                imgAlt = "In Progress";
                                break;
                            case '2':
                                imgAlt = "Passed";
                                break;
                            case '3':
                                imgAlt = "Failed";
                                break;
                        }


                        var courseStatusTop = '<img title="'+imgAlt+'" alt="'+imgAlt+'" src="'+overall_data['img']+'"/>';
            
                        $('#courseStatusTop span').html(courseStatusTop);
                        $('#saveButtonTop').html('<input class="btn btn-default btn-sm" type="button" id="saveGradeTop" value="Update Status">');
                        $('#saveButtonBottom').html('<input class="btn btn-default btn-sm" type="button" id="saveGradeBottom" value="Save">');
                    }
                    else
                    {
                        $('.alert-danger').html(response.message);
                        $('.alert-danger').show().focus();
                    }

                },
                error:function(jqXHR, textStatus, errorThrown){
                    $('.alert-danger').html(errorThrown);
                    $('.alert-danger').show().focus();
                }
            });
        }


        $(document).on("click", "#changeRevision", function () {
            original_val = $('#revision option[value="' + overall_data['revision'] + '"]').text()
            new_val = $( "#revision option:selected" ).text();
            if(original_val!==new_val) {
                $(".modal-body #originalRevision").html(original_val);
                $(".modal-body #newRevision").html(new_val);
                $('#confirm-revision-change').modal();
            }
        });

        $('body').on('click', '#confirmChangeRevision', function(event) {
            $('#grade-table tbody').empty();
            original_val = $('#revision option[value="' + overall_data['revision'] + '"]').text()
            new_val = $( "#revision option:selected" ).text();
            var formData = {
                'action'  : 'changeRevision',
                'usr_id'  : $( "#gradebook-users option:selected" ).val(),
                'ref_id': params['ref_id'],
                'old_revision':overall_data['revision'],
                'new_revision':$( "#revision option:selected" ).val()
            };
            $.ajax({
                type: "POST",
                data: formData,
                url: url,
                success: function(response) {
                    $('#confirm-revision-change').modal('toggle');
                    $(document).off("click", "#confirmChangeRevision");
                    getUserData($( "#gradebook-users option:selected" ).val());
                }
            });
        });


        $(document).on( 'click', '#saveGradeTop', function () {

            var formData = {
                'action'  : 'updateStatus',
                'user_id': $( "#gradebook-users option:selected" ).val(),
                'revision_id':$("#revision option:selected").val(),
                "overall_status":$('#overallStatusTop option:selected').val(),
                'ref_id': params['ref_id']
            };

            $.ajax({
                type: "POST",
                data: formData,
                url: url,
                beforeSend: function() { $('#saveGrade').prop('disabled', true); },
                complete: function() { $('#saveGrade').prop('disabled', false); },
                success: function(data) {
                    $('.alert-success').html('Successfully Saved')
                    $('.alert-success').show().focus();

                    getUserData($( "#gradebook-users option:selected" ).val());
                    switch (formData.overall_status) {
                        case '1':
                            $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/incomplete.svg');
                            break;
                        case '2':
                            $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/complete.svg');
                            break;
                        case '3':
                            $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/failed.svg');
                            break;
                    }

                },
                error:function(jqXHR, textStatus, errorThrown){

                    $('.alert-danger').html(errorThrown);
                    $('.alert-danger').show().focus();
                }
            });


        });


        $(document).on( 'click', '#saveGradeBottom', function () {

            $('.alert').hide();

            var objects = [];

            var validGrades = true;
            var invalidData ='';

            $( "#grade-table table tbody tr" ).each(function(index, element) {

                var row = $(this);

                $(row).find('.actual').removeClass('invalid-input');

                var retVal = {
                    "obj_id": $(row).attr('data-obj-id'),
                    "actual": ($(row).find('.actual').val() ? $(this).find('.actual').val() : ''),
                    "status": $(row).find(".obj-status option:selected").val()
                };

                if(isNaN(retVal.actual))
                {
                    $(row).find('.actual').addClass('invalid-input');
                    invalidData = 'Actual Grade is not a number';
                    validGrades = false;
                }
                else if(retVal.actual < 0 || retVal.actual > 100)
                {
                    $(row).find('.actual').addClass('invalid-input');
                    invalidData = 'Actual Grade is not within the range of 0-100';
                    validGrades = false;
                }

                objects.push(retVal);
            });

            if(validGrades)
            {
                var formData = {
                    'action'  : 'saveUsersGrades',
                    'grades'  : objects,
                    'user_id': $( "#gradebook-users option:selected" ).val(),
                    'revision_id':$("#revision option:selected").val(),
                    "overall_status":$('#overallStatus option:selected').val(),
                    'ref_id': params['ref_id']
                };

                $.ajax({
                    type: "POST",
                    data: formData,
                    url: url,
                    beforeSend: function() { $('#saveGrade').prop('disabled', true); },
                    complete: function() { $('#saveGrade').prop('disabled', false); },
                    success: function(data) {
                            $('.alert-success').html('Successfully Saved')
                            $('.alert-success').show().focus();

                            getUserData($( "#gradebook-users option:selected" ).val());
                            switch (formData.overall_status) {
                                case '1':
                                    $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/incomplete.svg');
                                    break;
                                case '2':
                                    $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/complete.svg');
                                    break;
                                case '3':
                                    $('#courseStatusBottom img').attr('src', './templates/default/images/scorm/failed.svg');
                                    break;
                            }

                    },
                    error:function(jqXHR, textStatus, errorThrown){

                        $('.alert-danger').html(errorThrown);
                        $('.alert-danger').show().focus();
                    }
                });
            }
            else
            {
                $('.alert-danger').html(invalidData);
                $('.alert-danger').show().focus();
            }


        });
    });

});
