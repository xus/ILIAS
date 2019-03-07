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

	/**
	 * Get mysql timestamp with the last HTML opening view date
	 * @param int $assignment_id
	 * @param string $extra_where
	 * @return string
	 */
	public function getLastOpeningHTMLView(int $assignment_id, string $extra_where);

	/**
	 * Get number of submissions from assignment id
	 * @param int $assignment_id
	 * @return int
	 */
	public function hasSubmissions(int $assignment_id): int;
}