il.MetaDataCopyrightListener  = {

	modalSignalId: "",
	radioGroupName: "",
	formId: "",
	formButtonId: "",

	confirmed: false,


	initialValue: "",


	init: function(modalSignalId, radioGroupName, formId, formButtonId ) {

		this.modalSignalId = modalSignalId;
		this.radioGroupName = radioGroupName;
		this.formId = formId;
		this.formButtonId = formButtonId;


		this.initialValue =
			$("input[name='" + this.radioGroupName + "']:checked").val();


		$("#" + this.formId).on(
			"submit",
			function (event) {

				var current_value =
					$("input[name='" + il.MetaDataCopyrightListener.radioGroupName + "']:checked").val();

				if(current_value != il.MetaDataCopyrightListener.initialValue) {

					if(!il.MetaDataCopyrightListener.confirmed) {
						event.preventDefault();
						il.MetaDataCopyrightListener.triggerModal(event);
					}
				}
			}
		);
	},

	triggerModal: function (event) {

		var buttonName = $('#' + il.MetaDataCopyrightListener.formButtonId).prop('value');
		$('.modal-dialog').find('form').find('input').prop('value',buttonName);
		$('.modal-dialog').find('form').on(
			'submit',
			function (event) {

				$('#' + il.MetaDataCopyrightListener.formId).off();
				$('#' + il.MetaDataCopyrightListener.formButtonId).off();
				il.MetaDataCopyrightListener.confirmed = true;
				$('#' + il.MetaDataCopyrightListener.formButtonId).click();
				return false;
			}
		);

		// Show modal
		$(document).trigger(
			il.MetaDataCopyrightListener.modalSignalId,
			{
				'id': this.modalSignalId,
				'event': event,
				'triggerer': this.formId
			}
		);



	}
};