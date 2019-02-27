<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository Interface
 *
 * @author Jesús López <lopez@leifos.com>
 */
interface ilExcSubmissionRepositoryInterface
{
	/**
	 * TODO phpdocs return string, submissiontype interface
	 * @return mixed
	 */
	public function getSubmissionType();

	/**
	 * Get User who submitted.
	 * @param int $submission_id
	 * @return int
	 */
	public function getUserId(int $submission_id): int;

	/**
	 * Get mysql timestamp with the last submission date.
	 * @param int $assignment_id
	 * @param string extra condition to add in the sql where clause
	 * @return string
	 */
	public function getLastSubmission(int $assignment_id, string $extra_where): string;

}