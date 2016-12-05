<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserRoleStartingPointTableGUI extends ilTable2GUI
{
	var $log;

	function __construct($a_parent_obj, $a_parent_cmd, $a_template_context)
	{
		global $ilCtrl, $lng;

		$this->setId("usrrolesp");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setLimit(9999);
		$this->setTitle($lng->txt("user_role_starting_point"));

		$this->addColumn("#");
		$this->addColumn($lng->txt("criteria"));
		$this->addColumn($lng->txt("starting_page"));
		$this->addColumn($lng->txt("actions"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.user_role_starting_point_row.html", "Services/User");

		$this->getItems();

	}

	/**
	 * Get data
	 */
	function getItems()
	{
		global $lng;

		include_once "Services/User/classes/class.ilUserUtil.php";

		$valid_points = ilUserUtil::getPossibleStartingPoints();

		$status = (ilUserUtil::hasPersonalStartingPoint()? "Individual" : "Default");

		$result = array();
		$result[] = array (
			"id" => "user",
			"criteria" => $lng->txt("user_chooses_starting_page"),
			"starting_page" => $status
		);

		require_once ("Services/AccessControl/classes/class.ilObjRole.php");

		$roles = ilObjRole::getRolesWithStartingPoint();

		foreach ($roles as $g_role)
		{
			$role = new ilObjRole($g_role['role_id']);



			$result[] = array (
				"id" => $role->getId(),
				"criteria" => $role->getTitle(),
				"starting_page" => $valid_points[$role->getStartingPoint()]
			);
		}

		$default_sp = ilUserUtil::getStartingPoint();
		$result[] = array (
			"id" => "default",
			"criteria" => $lng->txt("default"),
			"starting_page" => $valid_points[$default_sp]
		);

		$this->setData($result);

	}

	/**
	 * Fill a single data row.
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";

		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($lng->txt("actions"));

		if($a_set['id'] > 0 && $a_set['id'] != 'default' && $a_set['id'] != 'user')
		{
			$ilCtrl->setParameter($this->getParentObject(), "rolid", $a_set["id"]);

			$list->setId($a_set["id"]);

			$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "rolestartingpointform");
			$list->addItem($lng->txt("edit"), "", $edit_url);
			$delete_url = $ilCtrl->getLinkTarget($this->getParentObject(), "saveStartingPoint");
			$list->addItem($lng->txt("delete"), "", $delete_url);

			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("user_role_has_role").":".$a_set["criteria"]);
		}
		else
		{
			if($a_set['id'] == "default")
			{
				$ilCtrl->setParameter($this->getParentObject(), "rolid", "default");
				$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "rolestartingpointform");
			}
			else
			{
				$ilCtrl->setParameter($this->getParentObject(), "rolid", "user");
				$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "userstartingpointform");
			}

			$list->addItem($lng->txt("edit"), "", $edit_url);

			
			$this->tpl->setVariable("TXT_TITLE", $a_set["criteria"]);
		}

		$this->tpl->setVariable("TXT_PAGE", $a_set["starting_page"]);

		$this->tpl->setVariable("ACTION", $list->getHTML());
	}

}