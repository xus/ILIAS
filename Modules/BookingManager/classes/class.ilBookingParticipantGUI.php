<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBookingParticipantGUI
 *
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls ilBookingParticipantGUI: ilRepositorySearchGUI
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingParticipantGUI
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->access = $DIC->access();
		$this->toolbar = $DIC->toolbar();

		$this->ref_id = $a_parent_obj->ref_id;
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

		$next_class = $ilCtrl->getNextClass($this);

		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				$ref_id = $this->ref_id;
				$rep_search->addUserAccessFilterCallable(function ($a_user_id) use ($ref_id)
				{
					return $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
						'render',
						'render',
						$this->ref_id,
						$a_user_id
					);
				});
				$rep_search->setTitle($this->lng->txt("exc_add_participant"));
				$rep_search->setCallback($this,'addParticipantObject');
				$this->ctrl->setReturn($this,'render');
				$this->ctrl->forwardCommand($rep_search);
				break;

			default:
				$cmd = $ilCtrl->getCmd("render");
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Render list of booking participants.
	 *
	 * uses ilBookingParticipantsTableGUI
	 */
	function render()
	{
		if ($this->access->checkAccess('edit_permission', '', $this->ref_id))
		{
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->toolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add'),
					'add_search'			=> true,
					'add_from_container'    => $this->ref_id
				)
			);

			include_once 'Modules/BookingManager/classes/class.ilBookingParticipantsTableGUI.php';
			$table = new ilBookingParticipantsTableGUI($this, 'render');

		}

		//$html = $bar.table->getHTML();
		//$html = $table->getHTML();

		$this->tpl->setContent("Dummy content - Change me");

	}

	/**
	 * Add user as member
	 */
	public function addUserFromAutoCompleteObject()
	{
		if(!strlen(trim($_POST['user_login'])))
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
			$this->render();
			return false;
		}

		$users = explode(',', $_POST['user_login']);

		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);

			if(!$user_id)
			{
				ilUtil::sendFailure($this->lng->txt('user_not_known'));
				$this->render();
			}
			$user_ids[] = $user_id;
		}

		return $this->addParticipantObject($user_ids);
	}

	/**
	 * Add new partipant
	 * @param array $a_user_ids
	 * @return bool
	 */
	function addParticipantObject(array $a_user_ids)
	{
		foreach($a_user_ids as $user_id)
		{
			if(ilObject::_lookupType($user_id) === "usr")
			{
				require_once("./Modules/BookingManager/classes/class.ilBookingParticipant.php");

				$participant_obj = new ilBookingParticipant($user_id, $this->ref_id);
				if($participant_obj->getIsNew()) {
					ilUtil::sendSuccess($this->lng->txt("qp_participant_assigned"),true);
				} else {
					ilUtil::sendFailure($this->lng->txt("qp_participant_already_assigned"));
					return false;
				}
			}
			else
			{
				ilUtil::sendFailure("dummy error message, change me");
				return false;
			}
		}

		$this->ctrl->redirect($this, "render");
		return true;
	}

}

?>