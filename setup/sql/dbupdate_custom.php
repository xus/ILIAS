<#1>
<?php
	if($ilDB->tableColumnExists('exc_assignment', 'team_tutor'))
	{
		$ilDB->renameTableColumn('exc_assignment', 'team_tutor', 'team_formation');

		if($ilDB->tableColumnExists('exc_assignment', 'team_formation'))
		{
			$ilDB->modifyTableColumn('exc_assignment','team_formation',
				array("type" => "integer", "length" => 1, "notnull" => false));
		}
	}
?>
<#2>
<?php
	if(!$ilDB->tableColumnExists('exc_assignment', 'num_teams'))
	{
		$ilDB->addTableColumn('exc_assignment', "num_teams", array(
			"type" => 'integer',
			"length" => 1
		));
	}

	if(!$ilDB->tableColumnExists('exc_assignment', 'max_participants_team'))
	{
		$ilDB->addTableColumn('exc_assignment', "max_participants_team", array(
			"type" => 'integer',
			"length" => 1
		));
	}

	if(!$ilDB->tableColumnExists('exc_assignment', 'min_participants_team'))
	{
		$ilDB->addTableColumn('exc_assignment', "min_participants_team", array(
			"type" => 'integer',
			"length" => 1
		));
	}
?>
<#3>
<?php
	if(!$ilDB->tableColumnExists('exc_assignment', 'assignment_adopt'))
	{
		$ilDB->addTableColumn('exc_assignment', "assignment_adopt", array(
			"type" => 'integer',
			"length" => 1
		));
	}
?>