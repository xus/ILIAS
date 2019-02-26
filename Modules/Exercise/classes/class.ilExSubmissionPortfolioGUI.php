<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class to display portfolio submission view
 *
 * @author lopez@leifos.com
 * @ingroup ModulesExercise
 */
class ilExSubmissionPortfolioGUI
{

	protected $ctrl;
	/**
	 * Constructor
	 */
	function __construct(ilObjExercise $a_exercise, ilExSubmission $a_submission)
	{
		global $DIC;

		$this->ctrl = $DIC->database();
		
	}

	public function executeCommand()
	{
		$class = $this->ctrl->getNextClass($this);

		//TODO
	}


	public function showAssignmentPortfolioObject()
	{
		die("show assignment portfolio");
		//if( exc_returned  webdiraccess)
		//exportHTML
		//move the zip file to web directory
		//unzip the file
		//update the database

	}
}