<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class iStartingPointGUI
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */

class ilStartingPointGUI
{
	const ORDER_POSITION_MIN = 0;
	const ORDER_POSITION_MAX = 9999;

	protected $log;
	protected $lng;
	protected $tpl;
	protected $parent_ref_id;

	/**
	 * Constructor
	 * @access public
	 */
	function __construct($a_parent_ref_id)
	{
		global $lng,$tpl;

		$this->log = ilLoggerFactory::getLogger("user");
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->parent_ref_id = $a_parent_ref_id;

	}
	function &executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();

		if(!$cmd)
		{
			$cmd = "initRoleStartingPointForm";
		}

		$this->$cmd();

		return true;
	}

	/**
	 * table form to set up starting points depends of user roles
	 */
	public function startingPoints()
	{
		include_once "Services/User/classes/class.ilUserRoleStartingPointTableGUI.php";

		$tbl = new ilUserRoleStartingPointTableGUI($this);

		$this->tpl->setContent($tbl->getHTML());

	}

	public function initUserStartingPointForm(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getUserStartingPointForm();
		}
		$this->tpl->setContent($form->getHTML());
	}

	public function initRoleStartingPointForm(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getRoleStartingPointForm();
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function getUserStartingPointForm()
	{
		global $ilCtrl;

		require_once ("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once "Services/User/classes/class.ilUserUtil.php";

		$form = new ilPropertyFormGUI();

		// starting point: personal
		$startp = new ilCheckboxInputGUI($this->lng->txt("adm_user_starting_point_personal"), "usr_start_pers");
		$startp->setInfo($this->lng->txt("adm_user_starting_point_personal_info"));
		$startp->setChecked(ilUserUtil::hasPersonalStartingPoint());

		$form->addItem($startp);

		$form->addCommandButton("saveUserStartingPoint", $this->lng->txt("save"));
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getRoleStartingPointForm()
	{
		global $ilCtrl, $rbacsystem, $ilErr;

		if (!$rbacsystem->checkAccess("write",$this->parent_ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		require_once "./Services/AccessControl/classes/class.ilObjRole.php";
		include_once "Services/User/classes/class.ilUserUtil.php";

		$form = new ilPropertyFormGUI();

		$rolid = $_REQUEST['rolid'];

		//edit no default
		if($rolid > 0 && $rolid != 'default')
		{
			$role = new ilObjRole($rolid);
			$options[$rolid] = $role->getTitle();
			$si_roles = new ilSelectInputGUI($this->lng->txt("editing_this_role"), 'role');
			$starting_point = $role->getStartingPoint();
			$si_roles->setOptions($options);
			$form->addItem($si_roles);
		}
		//create
		elseif(!$rolid || $rolid !='default')
		{
			$roles = ilObjRole::getGlobalRolesWithoutStartingPoint();
			foreach($roles as $role)
			{
				$options[$role['id']] = $role['title'];
			}
			$si_roles = new ilSelectInputGUI($this->lng->txt("roles_without_starting_point"), 'role');
			$si_roles->setOptions($options);
			$form->addItem($si_roles);
		}
		else
		{
			$starting_point = ilUserUtil::getStartingPoint();
		}

		// starting point

		$si = new ilRadioGroupInputGUI($this->lng->txt("adm_user_starting_point"), "start_point");
		$si->setRequired(true);
		$si->setInfo($this->lng->txt("adm_user_starting_point_info"));
		$valid = array_keys(ilUserUtil::getPossibleStartingPoints());
		foreach(ilUserUtil::getPossibleStartingPoints(true) as $value => $caption)
		{
			$opt = new ilRadioOption($caption, $value);
			$si->addOption($opt);

			if(!in_array($value, $valid))
			{
				$opt->setInfo($this->lng->txt("adm_user_starting_point_invalid_info"));
			}
		}
		$si->setValue($starting_point);
		$form->addItem($si);

		// starting point: repository object
		$repobj = new ilRadioOption($this->lng->txt("adm_user_starting_point_object"), ilUserUtil::START_REPOSITORY_OBJ);
		$repobj_id = new ilTextInputGUI($this->lng->txt("adm_user_starting_point_ref_id"), "start_object");
		$repobj_id->setRequired(true);
		$repobj_id->setSize(5);
		//$i has the starting_point value, so we are here only when edit one role or setting the default role.
		if($si->getValue() == ilUserUtil::START_REPOSITORY_OBJ)
		{
			if($role)
			{
				$start_ref_id  = $role->getStartingObject();
			}
			else
			{
				$start_ref_id = ilUserUtil::getStartingObject();
			}

			$repobj_id->setValue($start_ref_id);
			if($start_ref_id)
			{
				$start_obj_id = ilObject::_lookupObjId($start_ref_id);
				if($start_obj_id)
				{
					$repobj_id->setInfo($this->lng->txt("obj_".ilObject::_lookupType($start_obj_id)).
						": ".ilObject::_lookupTitle($start_obj_id));
				}
			}
		}
		$repobj->addSubItem($repobj_id);
		$si->addOption($repobj);

		// save and cancel commands
		$form->addCommandButton("saveStartingPoint", $this->lng->txt("save"));

		$form->setTitle($this->lng->txt("starting_point_settings"));
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	protected function saveUserStartingPoint()
	{
		global $ilCtrl, $rbacsystem, $ilErr;

		if (!$rbacsystem->checkAccess("write",$this->parent_ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		include_once "Services/User/classes/class.ilUserUtil.php";

		$form = $this->getUserStartingPointForm();

		if ($form->checkInput())
		{
			ilUserUtil::togglePersonalStartingPoint($form->getInput('usr_start_pers'));
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "startingPoints");
		}
		ilUtil::sendFailure($this->lng->txt("msg_error"), true);
		$ilCtrl->redirect($this, "startingPoints");
	}

	/**
	 * store starting point from the form
	 */
	protected function saveStartingPoint()
	{
		global $ilCtrl, $tree, $rbacsystem, $ilErr;

		if (!$rbacsystem->checkAccess("write",$this->parent_ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		//add from form
		$form = $this->getRoleStartingPointForm();
		if ($form->checkInput())
		{
			//if role
			if($form->getInput('role'))
			{
				$this->log->debug("role =" .$form->getInput('role'));

				$role = new ilObjRole($form->getInput('role'));
				$role->setStartingPoint($form->getInput('start_point'));

				$obj_id = $form->getInput('start_object');
				if($obj_id && ($role->getStartingPoint() == ilUserUtil::START_REPOSITORY_OBJ))
				{
					if(ilObject::_lookupObjId($obj_id) && !$tree->isDeleted($obj_id))
					{
						$role->setStartingObject($obj_id);
						ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
					}
					else
					{
						ilUtil::sendFailure($this->lng->txt("obj_ref_id_not_exist"), true);
					}
				}
				else
				{
					$role->setStartingObject(0);
				}
				$role->update();
			}
			else  //default
			{
				ilUserUtil::setStartingPoint($form->getInput('start_point'), $form->getInput('start_object'));
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			}

			$ilCtrl->redirect($this, "startingPoints");
		}
		ilUtil::sendFailure($this->lng->txt("msg_error"), true);
		$ilCtrl->redirect($this, "startingPoints");
	}

	protected function saveOrder()
	{
		global $ilCtrl, $ilDB, $rbacsystem, $ilErr;

		if (!$rbacsystem->checkAccess("write",$this->parent_ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		foreach($_POST['position'] as $id => $position)
		{
			if($position > self::ORDER_POSITION_MIN && $position < self::ORDER_POSITION_MAX )
			{
				$sql = "UPDATE role_data".
					" SET starting_position = ".$ilDB->quote($position, 'integer').
					" WHERE role_id = ".$ilDB->quote($id, 'integer');
				$ilDB->query($sql);
			}
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$ilCtrl->redirect($this, "startingPoints");
	}
	/**
	 * Confirm delete starting point
	 */
	function confirmDeleteStartingPoint()
	{
		global $ilCtrl, $lng, $tpl, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('back_to_list'), $ilCtrl->getLinkTarget($this, 'startingPoints'));

		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('confirm_delete'));

		include_once "./Services/AccessControl/classes/class.ilObjRole.php";

		$rolid = (int)$_REQUEST['rolid'];
		$role = new ilObjRole($rolid);

		$conf->addItem('rolid', $rolid, $role->getTitle());
		$conf->setConfirm($lng->txt('delete'), 'deleteStartingPoint');
		$conf->setCancel($lng->txt('cancel'), 'startingPoints');

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Set to 0 the starting point values
	 */
	protected function deleteStartingPoint()
	{
		global $ilCtrl, $rbacsystem, $ilErr;

		if (!$rbacsystem->checkAccess("write", $this->parent_ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		require_once "./Services/AccessControl/classes/class.ilObjRole.php";

		$rolid = $_REQUEST['rolid'];

		$role = new ilObjRole($rolid);
		$role->setStartingPoint(0);
		$role->setStartingObject(0);
		$role->setStartingPosition(0);
		$role->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "startingPoints");
	}
}
?>