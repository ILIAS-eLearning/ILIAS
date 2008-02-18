function ilToggleVisibleElements()
{
	var migration = document.getElementById('acc_migrate');
	var userName = document.getElementById('acc_user_name');
	var userPass = document.getElementById('acc_user_pass');
	
	if(migration && !migration.checked)
	{
		userName.disabled = true;
		userPass.disabled = true;
	}
	else
	{
		userName.disabled = false;
		userPass.disabled = false;
	}
}

function ilCheckMigration()
{
	var migration = document.getElementById('acc_migrate');
	
	if(migration)
	{
		migration.checked = true;
	}
}
	
	