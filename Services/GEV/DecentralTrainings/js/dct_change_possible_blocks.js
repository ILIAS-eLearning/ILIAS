$(document).ready(function() {

	var selected_block = $('#blocks option:selected').val();

	if(typeof selected_block === "undefined") {
		$('#duration\\[start\\]\\[time\\]_h').attr('disabled',true);
		$('#duration\\[start\\]\\[time\\]_m').attr('disabled',true);
		$('#duration\\[end\\]\\[time\\]_h').attr('disabled',true);
		$('#duration\\[end\\]\\[time\\]_m').attr('disabled',true);
	}

	$('#wp').attr('readonly',true);

	$(document).on("change","select","", function(e) {
		var target_id = $(e.target).attr("id");
		
		switch(target_id) {
			case "topic": changeBuildingBlocks();
							break;
			case "blocks": changeBuildingBlockInfos();
						break;
			case "duration[start][time]_h":
			case "duration[start][time]_m":
			case "duration[end][time]_h":
			case "duration[end][time]_m":
							calculateCreditPoints();
			break;
		}
	});
});

/**
*change the elements in select input ui for building block
*
*/
function changeBuildingBlocks() {
	$('#duration\\[start\\]\\[time\\]_h').attr('disabled',true);
	$('#duration\\[start\\]\\[time\\]_m').attr('disabled',true);
	$('#duration\\[end\\]\\[time\\]_h').attr('disabled',true);
	$('#duration\\[end\\]\\[time\\]_m').attr('disabled',true);

	var selected = $('#topic option:selected').val();
	$.ajax({url: "create_decentral_training_data_json.php",data:"selected="+selected+"&type=0"}).done(function() {
		$('#blocks').empty();
		$('#content').val("");
		$('#target').val("");
		$('#wp').val("");

		$.getJSON("possible_bulding_blocks.json", function( data ) {
			var items = [];

			$.each(data, function(key,val) {
				items.push('<optgroup label="'+key+'">');

				$.each(val, function(key, val) {
					items.push('<option value="' + val[0] + '">' + val[1] + '</option>');
				});
			});
			$('#blocks').append(items.join(""));
		});
	});
}

/**
*change the building block information
*
*/
function changeBuildingBlockInfos() {
	$('#duration\\[start\\]\\[time\\]_h').attr('disabled',false);
	$('#duration\\[start\\]\\[time\\]_m').attr('disabled',false);
	$('#duration\\[end\\]\\[time\\]_h').attr('disabled',false);
	$('#duration\\[end\\]\\[time\\]_m').attr('disabled',false);

	var selected = $('#blocks option:selected').val();
	$.ajax({url: "create_decentral_training_data_json.php",data:"selected="+selected+"&type=1"}).done(function() {
		$('#content').val("");
		$('#target').val("");
		$('#wp').val("");
		
		$.getJSON("bulding_block_infos.json", function( data ) {
			var content = data["content"].replace("#:#","\n");
			var target = data["target"].replace("#:#","\n");

			$('#content').val(content);
			$('#target').val(target);
			$('#isWP').val(data["wp"]);
			$('#wp').val(0);
		});
	});
}

function calculateCreditPoints() {
	var isWP = $('#isWP').val();

	if(isWP == "Nein") {
		return;
	}

	var start_h = parseInt($('#duration\\[start\\]\\[time\\]_h option:selected').val());
	var start_m = parseInt($('#duration\\[start\\]\\[time\\]_m option:selected').val());
	var end_h = parseInt($('#duration\\[end\\]\\[time\\]_h option:selected').val());
	var end_m = parseInt($('#duration\\[end\\]\\[time\\]_m option:selected').val());

	var tot_h = 0;
	var tot_m = 0;

	if(end_m < start_m) {
		tot_h = -1;
		tot_m = end_m + (60 - start_m);
	} else {
		tot_m = end_m - start_m;
	}

	tot_h =  tot_h + (end_h - start_h);
	tot_m = tot_m + (tot_h * 60);
	tot_m = tot_m / 45;
	credit_points = tot_m.toFixed();

	$('#wp').val(credit_points);
}