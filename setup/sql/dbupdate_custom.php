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