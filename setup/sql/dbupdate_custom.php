<#1>
<?php
	if($ilDB->tableColumnExists('exc_assignment', 'team_tutor'))
	{
		$ilDB->renameTableColumn('exc_assignment', 'team_tutor', 'team_formation');
	}
?>
<#2>
<?php
	if(!$ilDB->tableColumnExists('exc_assignment', 'num_teams'))
	{
		$ilDB->addTableColumn('exc_assignment', "num_teams", array(
			"type" => 'integer',
			"length" => 1,
			"notnull" => false,
		));
	}

	if(!$ilDB->tableColumnExists('exc_assignment', 'max_participants_team'))
	{
		$ilDB->addTableColumn('exc_assignment', "max_participants_team", array(
			"type" => 'integer',
			"length" => 1,
			"notnull" => false,
		));
	}

	if(!$ilDB->tableColumnExists('exc_assignment', 'min_participants_team'))
	{
		$ilDB->addTableColumn('exc_assignment', "min_participants_team", array(
			"type" => 'integer',
			"length" => 1,
			"notnull" => false,
		));
	}
?>