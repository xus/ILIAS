<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * List objects / booking pool  assignment.
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingAssignObjectsTableGUI extends ilTable2GUI
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
	protected $user_id_to_book;

	/**
	 * @var ilObjBookingPool
	 */
	//protected $bp_object;

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
	 * @param	ilBookingParticipantGUI 	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	int		$a_user_id // user id to be assigned
	 */
	function __construct(ilBookingParticipantGUI $a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->access = $DIC->access();
		$this->ref_id = $a_ref_id;

		$this->pool_id = $a_pool_id;

		if($_GET['bkusr'])
		{
			$this->user_id_to_book = (int)$_GET['bkusr'];
			if(!ilObjUser::_exists($this->user_id_to_book)) {
				$this->ctrl->redirect($a_parent_obj, $a_parent_cmd);
			}
			$user_name_data = ilObjUser::_lookupName($this->user_id_to_book);
			$user_name = $user_name_data['lastname'].", ".$user_name_data['firstname'];
		} else {
			$this->ctrl->redirect($a_parent_obj, 'render');
		}

		$this->setId("bkaobj".$a_ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("book_assign_object").": ".$user_name);

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("description"));
		$this->addColumn($this->lng->txt("available"));
		$this->addColumn($this->lng->txt("action"));

		//Fix this order field
		//$this->setDefaultOrderField("title");
		//$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		//$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_assign_object_row.html", "Modules/BookingManager");
		//$this->setResetCommand("resetParticipantsFilter");
		//$this->setFilterCommand("applyParticipantsFilter");
		//$this->setDisableFilterHiding(true);

		//$this->initFilter();

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
		// filter for object if needed.
		/*if($this->filter["user_id"])
		{
			$filter["user_id"] = $this->filter["user_id"];
		}*/

		return $filter;
	}

	/**
	 * Gather data and build rows
	 * @param array $filter
	 */
	function getItems(array $filter = null)
	{
		$data = array();
		$obj_items = ilBookingObject::getList($this->pool_id);
		foreach($obj_items as $item)
		{
			if(empty(ilBookingReservation::getObjectReservationForUser($item['booking_object_id'], $this->user_id_to_book)))
			{
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', $this->user_id_to_book);
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $item['booking_object_id']);
				$data[] = array(
					'object_id' => $item['booking_object_id'],
					'title' => $item['title'],
					'description' => $item['description'],
					'nr_items' => ilBookingReservation::numAvailableFromObjectNoSchedule($item['booking_object_id']).'/'.$item['nr_items'],
					'url_assign'=> $this->ctrl->getLinkTargetByClass("ilbookingobjectgui", 'book')
				);
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', '');
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', '');
			}
		}
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("TXT_TITLE", $a_set['title']);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set['description']);
		$this->tpl->setVariable("TXT_AVAILABLE", $a_set['nr_items']);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("book_assign"));
		$this->tpl->setVariable("URL_ACTION", $a_set['url_assign']);
	}
}

?>