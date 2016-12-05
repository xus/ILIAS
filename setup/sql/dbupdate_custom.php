<#1>
<?php
if ($ilDB->tableExists('role_data'))
{
	if(!$ilDB->tableColumnExists('starting_point'))
	{
		$ilDB->addTableColumn("role_data", "starting_point", array("type" => "integer", "length" => 4, "notnull" => false, "default" => 0));
	}
}