<?php
/* Copyright (c) 2018 Extended GPL, see docs/LICENSE */

/**
 * Panels Handler for Exercise Submissions.
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExSubmissionPanelsHandlerGUI
{
	const PANEL_TYPE_SUBMISSION = 1;
	const PANEL_TYPE_REVISION = 2;

	const FEEDBACK_ONLY_SUBMISSION = "submission_only";
	const FEEDBACK_FULL_SUBMISSION = "submission_feedback";

	const GRADE_NOT_GRADED = "notgraded";
	const GRADE_PASSED = "passed";
	const GRADE_FAILED = "failed";

	/**
	 * @var ilExSubmission
	 */
	protected $submission;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilExAssignment
	 */
	protected $assignment;

	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $ui_factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $ui_renderer;

	/**
	 * @var array
	 */
	protected $filter;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var array
	 */
	protected $submissions_data;

	/**
	 * @var int
	 */
	protected $back_link;

	/**
	 * Constructor
	 * @param ilExAssignment $a_assignment
	 * @param integer $a_user_id
	 */
	public function __construct(ilExAssignment $a_assignment, int $a_user_id = null)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();
		$this->tabs = $DIC->tabs();
		$this->ui_factory = $DIC->ui()->factory();
		$this->ui_renderer = $DIC->ui()->renderer();
		$this->toolbar = $DIC->toolbar();
		$this->assignment = $a_assignment;

		//going back to submissions and grades tab as default behavior
		$this->back_link = $this->ctrl->getParentReturn($this);

		if($a_user_id)
		{
			$this->submission = new ilExSubmission($a_assignment, $a_user_id);
		}
		else
		{
			$this->submission = new ilExSubmission($a_assignment, $DIC->user()->getId());

			//going back to exercise assignments tab
			if($_GET['vw'] != ilExerciseManagementGUI::VIEW_GRADES) {
				$this->back_link = $this->ctrl->getLinkTargetByClass("ilObjExerciseGUI", "showOverview");
			}
		}
	}


	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$this->{$cmd."Object"}();
	}


	//TODO structure this data.
	public function setSubmissionsData($a_data)
	{
		$this->submissions_data = $a_data;
	}


	/**
	 * Display a list of panels with all versioned submissions.
	 */
	public function showVersionsObject()
	{
		$revision_obj = new ilExSubmissionRevision($this->submission);

		$submissions = $revision_obj->getRevisions();

		$this->showSubmissionPanels($this->lng->txt("exc_submission_list_versions"), self::PANEL_TYPE_REVISION, $submissions);
	}


	/**
	 * Display the HTML with a all submission panels
	 * @param string $a_title
	 * @param int $a_type
	 * @param array $a_submissions_data
	 */
	public function showSubmissionPanels(string $a_title, int $a_type, array $a_submissions_data)
	{
		$this->setBackLink();

		$group_panels_tpl = new ilTemplate("tpl.exc_group_report_panels.html", TRUE, TRUE, "Modules/Exercise");
		$group_panels_tpl->setVariable('TITLE', $a_title);

		$report_html = "";

		foreach($a_submissions_data as $submission_data)
		{
			//TODO : feedback data in the list of versions?? should I filter here? Feedback is not showed for versions
			$feedback_data = $this->collectFeedbackDataFromPeer($submission_data);
			$data = array_merge($feedback_data, $submission_data);
			$report_html .= $this->getReportPanel($a_type, $data);
		}

		$group_panels_tpl->setVariable('CONTENT', $report_html);
		$this->tpl->setContent($group_panels_tpl->get());
	}


	/**
	 * Add the Back link to the tabs. (used in submission list and submission compare)
	 * TODO -> Can we move this to ilExerciseManagementGUI? it will be better
	 */
	protected function setBackLink()
	{
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->lng->txt("back"),$this->back_link);
	}


	/**
	 * Filter initialization
	 * Filter by grade
	 * Display Feedback or not.
	 */
	function initFilter()
	{
		if($_POST["filter_status"]) {
			$this->filter["status"] = trim(ilUtil::stripSlashes($_POST["filter_status"]));
		}

		if($_POST["filter_feedback"]) {
			$this->filter["feedback"] = trim(ilUtil::stripSlashes($_POST["filter_feedback"]));
		}

		$this->lng->loadLanguageModule("search");

		$this->toolbar->setFormAction($this->ctrl->getFormAction($this, "listTextAssignment"));

		$si_status = new ilSelectInputGUI($this->lng->txt("exc_tbl_status"), "filter_status");
		$options = array(
			"" => $this->lng->txt("search_any"),
			self::GRADE_NOT_GRADED => $this->lng->txt("exc_notgraded"),
			self::GRADE_PASSED => $this->lng->txt("exc_passed"),
			self::GRADE_FAILED => $this->lng->txt("exc_failed")
		);
		$si_status->setOptions($options);
		$si_status->setValue($this->filter["status"]);

		$si_feedback = new ilSelectInputGUI($this->lng->txt("feedback"), "filter_feedback");
		$options = array(
			self::FEEDBACK_FULL_SUBMISSION => $this->lng->txt("submissions_feedback"),
			self::FEEDBACK_ONLY_SUBMISSION => $this->lng->txt("submissions_only")
		);
		$si_feedback->setOptions($options);
		$si_feedback->setValue($this->filter["feedback"]);

		$this->toolbar->addInputItem($si_status, true);
		$this->toolbar->addInputItem($si_feedback, true);

		//todo: old school here.
		include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
		$submit = ilSubmitButton::getInstance();
		$submit->setCaption("filter");
		$submit->setCommand("listTextAssignment");
		$this->toolbar->addButtonInstance($submit);
	}


	/**
	 * Display list of panels with ALL submissions from this assignment.
	 */
	function listTextAssignmentObject()
	{
		$this->initFilter();

		$button_print = $this->ui_factory->button()->standard($this->lng->txt('print'), "#")
			->withOnLoadCode(function($id) {
				return "$('#{$id}').click(function() { window.print(); return false; });";
			});
		$this->toolbar->addSeparator();
		$this->toolbar->addComponent($button_print);

		$title = $this->lng->txt("exc_list_text_assignment").": ".$this->assignment->getTitle();

		$submission_data = array();
		foreach(ilExSubmission::getAllAssignmentFiles($this->assignment->getExerciseId(), $this->assignment->getId()) as $file)
		{
			if(trim($file["atext"]))
			{
				$assignment_data = $this->assignment->getExerciseMemberAssignmentData($file["user_id"], $this->filter["status"]);
				if($assignment_data != '') {
					$submission_data[] = array_merge($file, $assignment_data);
				}
			}
		}
		if(count($submission_data) == 0)
		{
			$group_panels_tpl = new ilTemplate("tpl.exc_group_report_panels.html", TRUE, TRUE, "Modules/Exercise");
			$group_panels_tpl->setVariable('TITLE', $title);
			$mtpl = new ilTemplate("tpl.message.html", true, true, "Services/Utilities");
			$mtpl->setCurrentBlock("info_message");
			$mtpl->setVariable("TEXT", $this->lng->txt("fiter_no_results"));
			$mtpl->parseCurrentBlock();
			$report_html = $mtpl->get();

			$group_panels_tpl->setVariable('CONTENT', $report_html);
			$this->tpl->setContent($group_panels_tpl->get());
		}

		$this->showSubmissionPanels($title, self::PANEL_TYPE_SUBMISSION, $submission_data);
	}


	/**
	 * Display list of panels with submissions from different users.
	 */
	public function compareTextAssignmentsObject()
	{
		$this->showSubmissionPanels($this->lng->txt("exc_compare_selected_submissions"), self::PANEL_TYPE_SUBMISSION, $this->submissions_data);
	}


	/**
	 * Render a panel with the submission report.
	 * @param $a_type
	 * @param $a_data
	 * @return string
	 * @throws ilDateTimeException
	 */
	public function getReportPanel(int $a_type, array $a_data)
	{
		if($a_data['status'] == self::GRADE_NOT_GRADED) {
			$str_status_key = $this->lng->txt('exc_tbl_status');
			$str_status_value = $this->lng->txt('not_yet');
		} else {
			$str_status_key = $this->lng->txt('exc_tbl_status_time');
			$str_status_value = ilDatePresentation::formatDate(new ilDateTime($a_data["status_time"], IL_CAL_DATETIME));
		}

		if($a_data['feedback_time']) {
			$str_evaluation_key = $this->lng->txt('exc_tbl_feedback_time');
			$str_evaluation_value = ilDatePresentation::formatDate(new ilDateTime($a_data["feedback_time"], IL_CAL_DATETIME));
		} else {
			$str_evaluation_key = $this->lng->txt('exc_settings_feedback');
			$str_evaluation_value = $this->lng->txt('not_yet');
		}

		$card_content = array(
			$this->lng->txt("exc_tbl_submission_date") => ilDatePresentation::formatDate(new ilDateTime($a_data["udate"], IL_CAL_DATETIME)),
			$str_status_key => $str_status_value,
			$str_evaluation_key => $str_evaluation_value
		);
		if($this->displayFeedback($a_type))
		{
			$card_content[$this->lng->txt('feedback_given')] = $a_data['fb_given'];
			$card_content[$this->lng->txt('feedback_received')] = $a_data['fb_received'];
		}
		$card_tpl = new ilTemplate("tpl.exc_report_details_card.html", true, true, "Modules/Exercise");
		foreach($card_content as $key => $value)
		{
			$card_tpl->setCurrentBlock("assingment_card");
			$card_tpl->setVariable("ROW_KEY", $key);
			$card_tpl->setVariable("ROW_VALUE", $value);
			$card_tpl->parseCurrentBlock();
		}

		$main_panel = $this->ui_factory->panel()->sub($a_data['uname'], $this->ui_factory->legacy($a_data['utext']))
			->withCard($this->ui_factory->card()->standard($this->lng->txt('text_assignment'))->withSections(array($this->ui_factory->legacy($card_tpl->get()))));

		if($this->displayCardActions($a_type))
		{
			$modal = $this->getEvaluationModal($a_data);

			$actions = $this->ui_factory->dropdown()->standard(array(
				$this->ui_factory->button()->shy($this->lng->txt("grade_evaluate"), "#")->withOnClick($modal->getShowSignal()),
			));

			$main_panel = $main_panel->withActions($actions);
		}

		$feedback_tpl = new ilTemplate("tpl.exc_report_feedback.html", true, true, "Modules/Exercise");
		//if no feedback filter the feedback is displayed. Can be list submissions or compare submissions.
		if(array_key_exists("peer", $a_data) && ($this->filter["feedback"] == self::FEEDBACK_FULL_SUBMISSION) || $this->filter["feedback"] == "")
		{
			$feedback_tpl->setCurrentBlock("feedback");
			foreach($a_data["peer"] as $peer_id)
			{
				$user = new ilObjUser($peer_id);
				$peer_name =  $user->getFirstname()." ".$user->getLastname();

				$feedback_tpl->setCurrentBlock("peer_feedback");
				$feedback_tpl->setVariable("PEER_NAME", $peer_name);

				$submission = new ilExSubmission($this->assignment, $a_data["uid"]);
				$values = $submission->getPeerReview()->getPeerReviewValues($peer_id, $a_data["uid"]);

				$review_html = "";
				foreach($this->assignment->getPeerReviewCriteriaCatalogueItems() as $crit)
				{
					$crit_id = $crit->getId()
						? $crit->getId()
						: $crit->getType();
					$crit->setPeerReviewContext($this->assignment, $peer_id, $a_data["uid"]);

					$review_html .=
						'<div class="ilBlockPropertyCaption">'.$crit->getTitle().'</div>'.
						'<div style="margin:2px 0;">'.$crit->getHTML($values[$crit_id]).'</div>';

				}
				$feedback_tpl->setVariable("PEER_FEEDBACK", $review_html);
				$feedback_tpl->parseCurrentBlock();
			}
			$feedback_tpl->parseCurrentBlock();
		}
		$feedback_tpl->setVariable("GRADE", $this->lng->txt('grade').": ".$this->lng->txt('exc_'.$a_data['status']));
		$feedback_tpl->setVariable("COMMENT", $this->lng->txt('exc_comment')."<br>".$a_data['comment']);

		$feedback_panel = $this->ui_factory->panel()->sub("",$this->ui_factory->legacy($feedback_tpl->get()));

		$report = $this->ui_factory->panel()->report("", array($main_panel, $feedback_panel));

		if($this->displayCardActions($a_type))
		{
			return $this->ui_renderer->render([$modal,$report]);
		}

		return $this->ui_renderer->render($report);
	}


	/**
	 * Save assignment submission grade(status) and comment from the roundtrip modal.
	 */
	public function saveEvaluationFromModalObject()
	{
		$comment = trim($_POST['comment']);
		$user_id = (int)$_POST['mem_id'];
		$grade = trim($_POST["grade"]);
		$version_id = (int)$_POST['version_id'];

		// versioned/frozen submissions
		if ($version_id)
		{
			//update version
			$revision = new ilExSubmissionRevision($this->submission);
			$revision->updateRevisionStatus($version_id, $grade);
			$revision->updateRevisionComment($version_id, $comment);

			ilUtil::sendSuccess($this->lng->txt("exc_revision_updated"), true);
			$this->ctrl->redirect($this, "showVersions");
		}
		// last/current submission
		else
		{
			if ($this->assignment->getId() && $user_id)
			{
				$member_status = $this->assignment->getMemberStatus($user_id);
				$member_status->setComment(ilUtil::stripSlashes($comment));
				$member_status->setStatus($grade);
				if ($comment != "") {
					$member_status->setFeedback(true);
				}
				$member_status->update();
			}
			ilUtil::sendSuccess($this->lng->txt("exc_status_saved"), true);
			$this->ctrl->redirect($this, "listTextAssignment");
		}
	}


	/**
	 * Returns one modal containing a form where the submission can be graded/evaluated.
	 * @param $a_data
	 * @return \ILIAS\UI\Component\Modal\RoundTrip
	 */
	public function getEvaluationModal($a_data)
	{
		$modal_tpl = new ilTemplate("tpl.exc_report_evaluation_modal.html", true, true, "Modules/Exercise");
		$modal_tpl->setVariable("USER_NAME",$a_data['uname']);

		//TODO: CHECK ilias string utils. ilUtil shortenText with net blank.
		$max_chars = 500;

		//TODO the following show more text does not work properly


		$u_text = strip_tags($a_data["utext"]); //otherwise will get open P
		$text = $u_text;
		//show more
		if(strlen($u_text) > $max_chars)
		{
			$text = "<input type='checkbox' class='read-more-state' id='post-1' />";
			$text .= "<div class='read-more-wrap'>";
			$text .= mb_substr($u_text, 0, $max_chars);
			$text .= "<span class='read-more-target'>";
			$text .= mb_substr($u_text, $max_chars);
			$text .= "</span></div>";
			$text .= "<label for='post-1' class='read-more-trigger'></label>";
		}
		$modal_tpl->setVariable("USER_TEXT",$text);

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveEvaluationFromModal"));
		$form->setId(uniqid('form'));

		//Grade
		$options = array(
			self::GRADE_NOT_GRADED => $this->lng->txt("exc_notgraded"),
			self::GRADE_PASSED => $this->lng->txt("exc_passed"),
			self::GRADE_FAILED => $this->lng->txt("exc_failed")
		);
		$si = new ilSelectInputGUI($this->lng->txt("exc_tbl_status"), "grade");
		$si->setOptions($options);
		$si->setValue($a_data['status']);
		$form->addItem($si);

		$item = new ilHiddenInputGUI('mem_id');
		$item->setValue($a_data['uid']);
		$form->addItem($item);

		$version = new ilHiddenInputGUI('version_id');
		$version->setValue($a_data['version']);
		$form->addItem($version);

		$ta = new ilTextAreaInputGUI($this->lng->txt("exc_comment"), 'comment');
		$ta->setInfo($this->lng->txt("exc_comment_for_learner_info"));
		$ta->setValue($a_data['comment']);
		$ta->setRows(10);
		$form->addItem($ta);

		$modal_tpl->setVariable("FORM",$form->getHTML());

		$form_id = 'form_' . $form->getId();
		$submit_btn = $this->ui_factory->button()->primary($this->lng->txt("save"), '#')
			->withOnLoadCode(function($id) use ($form_id) {
				return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
			});

		return  $this->ui_factory->modal()->roundtrip(strtoupper($this->lng->txt("grade_evaluate")), $this->ui_factory->legacy($modal_tpl->get()))->withActionButtons([$submit_btn]);
	}


	/**
	 * Returns an array with the provided feedback
	 * @param $a_data array submission data
	 * @return $data array
	 */
	public function collectFeedbackDataFromPeer(array $a_data): array
	{
		$user = new ilObjUser($a_data["user_id"]);
		$uname = $user->getFirstname()." ".$user->getLastname();

		$data = array(
			"uid" => $a_data["user_id"],
			"uname" => $uname,
			"udate" => $a_data["ts"],
			"utext" => ilRTE::_replaceMediaObjectImageSrc($a_data["atext"], 1) // mob id to mob src
		);

		//get data peer and assign it
		$peer_review = new ilExPeerReview($this->assignment);
		$data["peer"] = array();
		foreach($peer_review->getPeerReviewsByPeerId($a_data['user_id']) as $key => $value)
		{
			$data["peer"][] = $value['giver_id'];
		}

		$data["fb_received"] = count($data["peer"]);
		$data["fb_given"] = $peer_review->countGivenFeedback(true, $a_data["user_id"]);

		return $data;
	}


	//TODO the confirmation can be moved to ilExerciseManagementGUI
	/**
	 * Confirm create a new version of the submission.
	 */
	function confirmFreezeSubmissionObject()
	{
		$user_id = (int)$_GET['usr_id'];

		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("exc_msg_sure_to_freeze_submission"));
		$cgui->setCancel($this->lng->txt("cancel"), "members");
		$cgui->setConfirm($this->lng->txt("confirm"), "freezeVersion");

		$cgui->addItem("usr_id", $user_id,
			ilUserUtil::getNamePresentation((int) $user_id, false, false, "", true));

		$this->tpl->setContent($cgui->getHTML());
	}

	/**
	 * @param $type
	 * @return bool
	 */
	function displayCardActions(int $type): bool
	{
		if($type == self::PANEL_TYPE_SUBMISSION || $type == self::PANEL_TYPE_REVISION)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param int $type
	 * @return bool
	 */
	function displayFeedback(int $type): bool
	{
		if($type == self::PANEL_TYPE_SUBMISSION)
		{
			return true;
		}

		return false;
	}
}