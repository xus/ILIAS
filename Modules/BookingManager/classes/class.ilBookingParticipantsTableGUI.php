<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	protected $filter; // [array]
	protected $objects; // array

	/**
	 * Constructor
	 * @param	ilBookingParticipantGUI 	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
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

		$this->setId("bkprt".$a_ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("book_participants_list"));

		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("bk_object"));
		$this->addColumn($this->lng->txt("action"));

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_participant_row.html", "Modules/BookingManager");
		$this->setResetCommand("resetParticipantsFilter");
		$this->setFilterCommand("applyParticipantsFilter");
		$this->setDisableFilterHiding(true);

		$this->initFilter();

		$this->getItems($this->getCurrentFilter());
	}

	function initFilter()
	{
		//object
		$this->objects = array();
		foreach(ilBookingObject::getList($this->pool_id) as $item)
		{
			$this->objects[$item["booking_object_id"]] = $item["title"];
		}
		$item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
		$item->setOptions(array(""=>$this->lng->txt('book_all'))+$this->objects);
		$this->filter["object"] = $item->getValue();

		$title = $this->addFilterItemByMetaType(
			"title",
			ilTable2GUI::FILTER_TEXT,
			false,
			$this->lng->txt("object")." ".$this->lng->txt("title")."/".$this->lng->txt("description")
		);
		$this->filter["title"] = $title->getValue();

		//user
		require_once("./Modules/BookingManager/classes/class.ilBookingParticipant.php");
		$options = array(""=>$this->lng->txt('book_all'))+
			ilBookingParticipant::getUserFilter($this->pool_id);
		$item = $this->addFilterItemByMetaType("user", ilTable2GUI::FILTER_SELECT);
		$item->setOptions($options);
		$this->filter["user_id"] = $item->getValue();
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		$filter = array();
		if($this->filter["object"])
		{
			$filter["object"] = $this->filter["object"];
		}
		if($this->filter["title"])
		{
			$filter["title"] = $this->filter["title"];
		}
		if($this->filter["user_id"])
		{
			$filter["user_id"] = $this->filter["user_id"];
		}

		return $filter;
	}

	/**
	 * Gather data and build rows
	 */
	function getItems(array $filter)
	{
		if(!$filter["object"])
		{
			$ids = array_keys($this->objects);
		}
		else
		{
			$ids = array($filter["object"]);
		}

		// TODO DEFINE THE SHOW ALL
		//if(!$this->show_all)
		//{
		//	$filter["user_id"] = $this->user->getId();
		//}

		include_once "Modules/BookingManager/classes/class.ilBookingParticipant.php";
		$data = ilBookingParticipant::getList($this->pool_id, $ids, $filter);

		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		//$selected = $this->getSelectedColumns();
		$this->tpl->setVariable("TXT_NAME", $a_set['name']);
		$this->tpl->setVariable("TXT_OBJECT", $a_set['object_id']);
		$this->tpl->setVariable("TXT_ACTION", $a_set['actions']);
	}
}

?>