<#1>
<?php
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