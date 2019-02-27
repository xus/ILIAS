<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Modules/Exercise/Submission/classes/class.ilExcSubmissionInterface.php");

/**
 * Submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExSubmissionRepository implements ilExcSubmissionRepositoryInterface
{
	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * ilExSubmissionRepository constructor.
	 * @param ilDBInterface $db
	 */
	public function __construct(ilDBInterface $db = null)
	{
		global $DIC;

		$this->db = (is_null($db))
			? $DIC->database()
			: $db;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubmissionType()
	{
		//TODO
	}

	/**
	 * @inheritdoc
	 */
	public function getUserId(int $submission_id): int
	{
		$q = "SELECT user_id FROM exc_returned".
			" WHERE returned_id = ".$this->db->quote($submisison_id, "integer");
		$usr_set = $this->db->query($q);
		return $this->db->fetchAssoc($usr_set);
	}

	/**
	 * @inheritdoc
	 */
	public function getLastSubmission(int $assignment_id, string $extra_where): string
	{
		$this->db->setLimit(1);

		$q = "SELECT ts FROM exc_returned".
			" WHERE ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND ts IS NOT NULL".
			" AND ".$extra_where.
			" ORDER BY ts DESC";

		$usr_set = $this->db->query($q);

		$array = $this->db->fetchAssoc($usr_set);

		return ilUtil::getMySQLTimestamp($array["ts"]);
	}
}