<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * List participant / booking pool  assignment.
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingAssignParticipantsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var int
	 */
	protected $pool_id;

	/**
	 * @var int
	 */
	protected $bp_object_id;

	/**
	 * @var ilObjBookingPool
	 */
	protected $bp_object;

	/**
	 * @var int
	 */
	protected $current_bookings; // [int]

	/**
	 * @var array
	 */
	protected $filter; // [array]

	/**
	 * @var array
	 */
	protected $objects; // array

	//TODO clean unused vars.
	protected $has_schedule;	// [bool]
	protected $may_edit;	// [bool]
	protected $may_assign; // [bool]
	protected $overall_limit;	// [int]
	protected $reservations = array();	// [array]

	/**
	 * Constructor
	 * @param	ilBookingObjectGUI 	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	int		$a_booking_obj_id //booking object to assign users.
	 */
	function __construct(ilBookingObjectGUI $a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_booking_obj_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->access = $DIC->access();
		$this->ref_id = $a_ref_id;
		$this->bp_object_id = $a_booking_obj_id;
		$this->pool_id = $a_pool_id;
		$this->bp_object = new ilBookingObject($a_booking_obj_id);

		$this->setId("bkaprt".$a_ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("book_assign_participant").": ".$this->bp_object->getTitle());

		$this->addColumn("", "");
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("bk_object"));
		$this->addColumn($this->lng->txt("action"));

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_assign_participant_row.html", "Modules/BookingManager");
		//$this->setResetCommand("resetParticipantsFilter");
		//$this->setFilterCommand("applyParticipantsFilter");
		//$this->setDisableFilterHiding(true);

		$this->initFilter();

		$this->getItems($this->getCurrentFilter());

	}

	//TODO implement the filters if any.
	function initFilter()
	{
		return array();
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		$filter = array();

		if($this->filter["user_id"])
		{
			$filter["user_id"] = $this->filter["user_id"];
		}

		return $filter;
	}

	/**
	 * Gather data and build rows
	 * @param array $filter
	 */
	function getItems(array $filter = null)
	{
		include_once "Modules/BookingManager/classes/class.ilBookingParticipant.php";
		$data = ilBookingParticipant::getList($this->pool_id, $filter);

		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("TXT_NAME", $a_set['name']);
		foreach($a_set['object_title'] as $obj_title)
		{
			$this->tpl->setCurrentBlock('object_titles');
			$this->tpl->setVariable("TXT_OBJECT", $obj_title);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("TXT_ACTION", $a_set['txt_action']);
		$this->tpl->setVariable("URL_ACTION", $a_set['url_action']);

	}
}

?>