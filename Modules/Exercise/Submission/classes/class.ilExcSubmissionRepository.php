<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionRepository implements ilExcSubmissionRepositoryInterface
{
	const TABLE_NAME = "exc_returned";

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
		$q = "SELECT user_id FROM ".self::TABLE_NAME.
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

		$q = "SELECT ts FROM ".self::TABLE_NAME.
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

		$q = "SELECT web_dir_access_time FROM ".self::TABLE_NAME.
			" WHERE ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND web_dir_access_time IS NOT NULL".
			" AND ".$extra_where.
			" ORDER BY web_dir_access_time DESC";

		$res = $this->db->query($q);

		$data = $this->db->fetchAssoc($res);

		return ilUtil::getMySQLTimestamp($data["web_dir_access_time"]);
	}

	/**
	 * @inheritdoc
	 */
	public function hasSubmissions(int $ass_id): int
	{
		$query = "SELECT * FROM ".self::TABLE_NAME.
			" WHERE ass_id = ".$this->db->quote($ass_id, "integer").
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND ts IS NOT NULL";
		$res = $this->db->query($query);
		return $res->numRows($res);
	}

	/**
	 * Update web_dir_access_time. It defines last HTML opening data.
	 * @param int $assignment_id
	 * @param int $member_id
	 */
	public function updateWebDirAccessTime(int $assignment_id, int $member_id)
	{
		$this->db->manipulate("UPDATE ".self::TABLE_NAME.
			" SET web_dir_access_time = ".$this->db->quote(ilUtil::now(), "timestamp").
			" WHERE ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND user_id = ".$this->db->quote($member_id, "integer"));
	}
}