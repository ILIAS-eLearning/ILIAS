il.CalendarAppointmentNotificationToggler = {
	init(calSelectID, notificationCals, checkboxName) {
		const calSelect = document.getElementById(calSelectID).querySelector('select');

		calSelect.onchange =  function() {
			const index = calSelect.selectedIndex;
			const value = calSelect.options[index].value;
			const checkbox = document.querySelector('input[name$="' + checkboxName + '"]');

			for(var i = 0; i < notificationCals.length; i++)
			{
				if(notificationCals[i] == value)
				{
					checkbox.disabled = false;
					return;
				}
			}
			checkbox.disabled = true;
			checkbox.checked = false;
			return;
		}
	}
}
