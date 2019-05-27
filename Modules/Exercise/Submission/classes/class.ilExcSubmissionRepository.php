<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository
 * //TODO: submission repository methods MUST return submission object or one related data structure.
 * //TODO: rename methods to something more specific.
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionRepository implements ilExcSubmissionRepositoryInterface
{
	const TABLE_NAME = "exc_returned";

	// TODO another table here??? smells?¿
	const TABLE_USER_TUTOR = "exc_usr_tutor";

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * ilExcSubmissionRepository constructor.
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
		$q = "SELECT user_id FROM " . self::TABLE_NAME .
			" WHERE returned_id = " . $this->db->quote($submission_id, "integer");

		$usr_set = $this->db->query($q);

		return $this->db->fetchAssoc($usr_set);
	}

	/**
	 * @inheritdoc
	 */
	public function hasSubmissions(int $ass_id): int
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE ass_id = " . $this->db->quote($ass_id, "integer") .
			" AND (filename IS NOT NULL OR atext IS NOT NULL)" .
			" AND ts IS NOT NULL";

		$res = $this->db->query($query);

		return (int)$res->numRows($res);
	}

	/**
	 * Update web_dir_access_time. It defines last HTML opening data.
	 * @param int $assignment_id
	 * @param int $member_id
	 */
	public function updateWebDirAccessTime(int $assignment_id, int $member_id)
	{
		$this->db->manipulate("UPDATE " . self::TABLE_NAME .
			" SET web_dir_access_time = " . $this->db->quote(ilUtil::now(), "timestamp") .
			" WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND user_id = " . $this->db->quote($member_id, "integer"));
	}

	public function getAllByAssignmentId($a_ass_id)
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE ass_id = " .
			$this->db->quote($a_ass_id, "integer");

		return $this->db->query($query);
	}

	public function getAllByUserIds(int $ass_id, array $user_ids)
	{
		$query = "SELECT * FROM".self::TABLE_NAME .
			" WHERE ass_id = " .
			$this->db->quote($ass_id, "integer") .
			" AND user_id IN (" . implode(',' , $user_ids) . ")";

		return $this->db->query($query);
	}

	public function getExerciseIdByReturnedId(int $returned_id)
	{
		$query = "SELECT obj_id FROM " . self::TABLE_NAME.
			" WHERE returned_id = " . $this->db->quote($returned_id, "integer");

		return $this->db->query($query);
	}

	public function getAssignmentParticipants(int $assignment_id, int $exercise_id)
	{
		$query = "SELECT user_id FROM " . self::TABLE_NAME .
			" WHERE ass_id = " .
			$this->db->quote($assignment_id, "integer") .
			" AND obj_id = " .
			$this->db->quote($exercise_id, "integer");

		return $this->db->query($query);
	}

	public function getSubmissionsByFilename(string $filename, array $assignment_types)
	{
		$query = "SELECT * FROM " . self::TABLE_NAME. " r" .
			" LEFT JOIN exc_assignment a" .
			" ON (r.ass_id = a.id) " .
			" WHERE r.filetitle = " . $this->db->quote($filename, "string");

		if (is_array($assignment_types) && count($assignment_types) > 0)
		{
			$query .= " AND " . $this->db->in("a.type", $assignment_types, false, "integer");
		}

		return $this->db->query($query);
	}

	/**
	 * @param $id int
	 * @param $text string
	 */
	public function updateSubmittedText(int $id, string $text): void
	{
		$this->db->manipulate("UPDATE ".self::TABLE_NAME .
			" SET atext = " . $this->db->quote($text, "text") .
			", ts = " . $this->db->quote(ilUtil::now(), "timestamp") .
			", late = " . $this->db->quote($this->isLate(), "integer") .
			" WHERE returned_id = " . $this->db->quote($id, "integer"));
	}

	public function getSubmissionByUserIdAndFileTitle(int $user_id, string $filetitle)
	{
		$query = "SELECT obj_id, ass_id" .
			" FROM " . self::TABLE_NAME.
			" WHERE user_id = " . $this->db->quote($user_id, "integer") .
			" AND filetitle = " . $this->db->quote($filetitle, "text");

		return $this->db->query($query);
	}

	//TODO most probably this method has to be removed from this class
	public function getLastDownloadTime(int $assignment_id, array $user_ids, int $tutor_id)
	{
		$query = "SELECT download_time FROM ".self::TABLE_USER_TUTOR .
 			" WHERE  ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in("usr_id", $user_ids, "", "integer") .
			" AND tutor_id = " . $this->db->quote($tutor_id, "integer");

		$lu_set = $this->db->query($query);
		$lu_rec = $this->db->fetchAssoc($lu_set);

		return $lu_rec["download_time"];
	}

	//TODO to much parameters
	public function insertFile($exercise_id, $assignment_id, $user_id, $team_id, $post_file_name, $result_fullname, $result_mimetype, $is_late)
	{
		$next_id = $this->db->nextId(self::TABLE_NAME);

		$query = sprintf("INSERT INTO " . self::TABLE_NAME .
			" (returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, late, team_id) " .
			"VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			$this->db->quote($next_id, "integer"),
			$this->db->quote($exercise_id, "integer"),
			$this->db->quote($user_id, "integer"),
			$this->db->quote($result_fullname, "text"),
			$this->db->quote(ilFileUtils::getValidFilename($post_file_name), "text"),
			$this->db->quote($result_mimetype, "text"),
			$this->db->quote(ilUtil::now(), "timestamp"),
			$this->db->quote($assignment_id, "integer"),
			$this->db->quote($is_late, "integer"),
			$this->db->quote($team_id, "integer")
		);

		$this->db->manipulate($query);
	}

	// TODO convert this to data object
	public function insert(array $data) : int
	{
		$next_id = $this->db->nextId("exc_returned");

		$query = "INSERT INTO " . self::TABLE_NAME .
			" (returned_id, obj_id, user_id, filetitle, ass_id, ts, atext, late, team_id)" .
			" VALUES (" .
			$this->db->quote($next_id, "integer") . ", " .
			$this->db->quote($data['obj_id'], "integer") . ", " .
			$this->db->quote($data['user_id'], "integer") . ", " .
			$this->db->quote($data['filetitle'], "text") . ", " .
			$this->db->quote($data['ass_id'], "integer") . ", " .
			$this->db->quote($data['ts'], "timestamp") . ", " .
			$this->db->quote($data['atext'], "text") . ", " .
			$this->db->quote($data['late'], "integer") . ", " .
			$this->db->quote($data['team_id'], "integer") .
			")";

		$this->db->manipulate($query);

		return $next_id;
	}
}