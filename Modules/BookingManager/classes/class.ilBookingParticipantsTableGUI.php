<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

//include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List booking participants
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingParticipantsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	protected $ref_id; // [int]
	protected $pool_id;	// [int]
	protected $has_schedule;	// [bool]
	protected $may_edit;	// [bool]
	protected $may_assign; // [bool]
	protected $overall_limit;	// [int]
	protected $reservations = array();	// [array]
	protected $current_bookings; // [int]
	protected $advmd; // [array]
	protected $filter; // [array]

	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	bool	$a_pool_has_schedule
	 * @param	int		$a_pool_overall_limit
	 */
	//function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_pool_has_schedule, $a_pool_overall_limit)
	function __construct($a_parent_obj, $a_parent_cmd)
	{

	}

	/**
	 * needed for advmd filter handling
	 *
	 * @return ilAdvancedMDRecordGUI
	 */
	protected function getAdvMDRecordGUI()
	{
		// #16827

	}

	function initFilter()
	{

	}

	/**
	 * Gather data and build rows
	 */
	function getItems()
	{
		//$this->setMaxCount(sizeof($data));
		//$this->setData($data);
	}

	function numericOrdering($a_field)
	{

	}

	function getSelectableColumns()
	{

	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{

	}
}

?>