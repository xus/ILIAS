<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionRepository implements ilExcSubmissionRepositoryInterface
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
	public function getUserId(int $submission_id): int
	{
		$q = "SELECT user_id FROM exc_returned".
			" WHERE returned_id = ".$this->db->quote($submission_id, "integer");
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

		$res = $this->db->query($q);

		$data = $this->db->fetchAssoc($res);

		return ilUtil::getMySQLTimestamp($data["ts"]);
	}

	/**
	 * @inheritdoc
	 */
	public function getLastOpeningHTMLView(int $assignment_id, string $extra_where)
	{
		$this->db->setLimit(1);

		$q = "SELECT web_dir_access_time FROM exc_returned".
			" WHERE ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND web_dir_access_time IS NOT NULL".
			" AND ".$extra_where.
			" ORDER BY web_dir_access_time DESC";

		$res = $this->db->query($q);

		$data = $this->db->fetchAssoc($res);

		return ilUtil::getMySQLTimestamp($data["web_dir_access_time"]);
	}
}