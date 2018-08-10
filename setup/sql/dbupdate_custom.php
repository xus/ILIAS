<#1>
<?php
if (!$ilDB->tableExists('il_booking_member'))
{
	$ilDB->createTable('il_booking_member', array(
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
	$ilDB->addPrimaryKey('il_booking_member', array('participant_id', 'user_id', 'booking_pool_id'));
	$ilDB->createSequence('il_booking_member');
}
?>