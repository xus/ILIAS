<#1>
<?php
if(!$ilDB->tableColumnExists('exc_returned', 'web_dir_access'))
{
	$ilDB->addTableColumn('exc_returned', 'web_dir_access', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}

$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
if($ilDB->tableColumnExists('exc_returned', 'web_dir_access'))
{
	$ilDB->dropTableColumn('exc_returned', 'web_dir_access');
}

if(!$ilDB->tableColumnExists('exc_returned', 'web_dir_access_time'))
{
	$ilDB->addTableColumn('exc_returned', 'web_dir_access_time', array(
		'type' => 'timestamp',
		'notnull' => false,
		'default' => null
	));
}
$ilCtrlStructureReader->getStructure();
?>