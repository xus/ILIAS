<#1>
<?php
if (!$ilDB->tableExists("exc_submission_version"))
{
	$ilDB->createTable("exc_submission_version", array(
			'id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'returned_id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'obj_id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'user_id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'filename' => array(
				'type'		=> 'text',
				'length'	=> 1000,
				'notnull'	=> false
			),
			'filetitle' => array(
				'type'		=> 'text',
				'length'	=> 1000,
				'notnull'	=> false
			),
			'mimetype' => array(
				'type'		=> 'text',
				'length'	=> 150,
				'notnull'	=> false
			),
			'ts' => array(
				'type'		=> 'timestamp',
				'notnull'	=> false
			),
			'ass_id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'atext' => array(
				'type'		=> 'text',
				'length'	=> 4000,
				'notnull'	=> true
			),
			'late' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'team_id' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> false
			),
			'status' => array(
				'type'		=> 'text',
				'length'	=> '9'
			),
			'status_time' => array(
				'type'		=> 'timestamp',
				'notnull'	=> false
			),
			'mark' => array(
				'type'		=> 'text',
				'length'	=> '32'
			),
			'u_comment' => array(
				'type'		=> 'text',
				'length'	=> '4000'
			),
			'version' => array(
				'type'		=> 'integer',
				'length'	=> 4,
				'notnull'	=> true
			),
			'versioned' => array(
				'type'		=> 'timestamp',
				'notnull'	=> true
			)
		)
	);

	$ilDB->addPrimaryKey('exc_submission_version', array('id', 'user_id', 'team_id', 'ass_id'));
	$ilDB->createSequence('exc_submission_version');
}
?>
<#2>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if (!$ilDB->tableColumnExists("exc_submission_version","feedback_time"))
{
	$atts = array(
		'type' => 'timestamp',
		'notnull' => false,
	);
	$ilDB->addTableColumn("exc_submission_version","feedback_time",$atts);
}
?>