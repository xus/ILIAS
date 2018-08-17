<#1>
<?php
if (!$ilDB->tableExists('booking_member'))
{
	$ilDB->createTable('booking_member', array(
		'participant_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'booking_pool_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'assigner_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('booking_member', array('participant_id', 'user_id', 'booking_pool_id'));
	$ilDB->createSequence('booking_member');
}
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('booking_reservation','assigner_id'))
{
	$ilDB->addTableColumn("booking_reservation", "assigner_id", array("type" => "integer", "length" => 2, "notnull" => true, "default" => 0));
}
?>
