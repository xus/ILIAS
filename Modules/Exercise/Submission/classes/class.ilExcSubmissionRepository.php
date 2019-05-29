<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Persistence Layer - Submission Repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionRepository implements ilExcSubmissionRepositoryInterface
{
	const TABLE_NAME = "exc_returned";

	const COL_USER_ID = "user_id";
	const COL_RETURNED_ID = "returned_id";
	const COL_ASS_ID = "ass_id";
	const COL_FILENAME = "filename";
	const COL_ATEXT = "atext";
	const COL_TS = "ts";
	const COL_WEB_DIR_ACCESS_TIME = "web_dir_access_time";
	const COL_OBJ_ID = "obj_id";
	const COL_FILETITLE = "filetitle";
	const COL_LATE = "late";
	const COL_MIMETYPE = "mimetype";
	const COL_TEAM_ID = "team_id";

	// TODO another table here??? code smell?¿
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
		$q = "SELECT " . self::COL_USER_ID . " FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_RETURNED_ID . " = " . $this->db->quote($submission_id, "integer");

		$usr_set = $this->db->query($q);

		return $this->db->fetchAssoc($usr_set);
	}

	/**
	 * @inheritdoc
	 */
	public function hasSubmissions(int $ass_id): int
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($ass_id, "integer") .
			" AND (" . self::COL_FILENAME . " IS NOT NULL OR " . self::COL_ATEXT ." IS NOT NULL)" .
			" AND " . self::COL_TS . " IS NOT NULL";

		$res = $this->db->query($query);

		return (int)$res->numRows();
	}

	/**
	 * Update web_dir_access_time. It defines last HTML opening data.
	 * @param int $assignment_id
	 * @param int $member_id
	 */
	public function updateWebDirAccessTime(int $assignment_id, int $member_id)
	{
		$this->db->manipulate("UPDATE " . self::TABLE_NAME .
			" SET " . self::COL_WEB_DIR_ACCESS_TIME . " = " . $this->db->quote(ilUtil::now(), "timestamp") .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_USER_ID . " = " . $this->db->quote($member_id, "integer"));
	}

	public function getAllByAssignmentId($a_ass_id)
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($a_ass_id, "integer");

		$result = $this->db->query($query);

		return $this->db->fetchAll($result);
	}

	public function getAllByUserIds(int $ass_id, array $user_ids)
	{
		$query = "SELECT * FROM ".self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($ass_id, "integer") .
			" AND " . self::COL_USER_ID . " IN (" . implode(',' , $user_ids) . ")";

		$result = $this->db->query($query);

		return $this->db->fetchAll($result);
	}

	public function getExerciseIdByReturnedId(int $returned_id)
	{
		$query = "SELECT " . self::COL_OBJ_ID . " FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_RETURNED_ID . " = " . $this->db->quote($returned_id, "integer");

		$result = $this->db->query($query);

		$row = $this->db->fetchAssoc($result);

		return $row[self::COL_OBJ_ID];

	}

	public function getAssignmentParticipants(int $exercise_id, int $assignment_id) : array
	{
		$query = "SELECT " . self::COL_USER_ID . " FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_OBJ_ID . " = " .
			$this->db->quote($exercise_id, "integer");

		$results = $this->db->query($query);

		$participants = array();
		while($row = $this->db->fetchAssoc($results))
		{
			$participants[] = $row[self::COL_USER_ID];
		}

		return $participants;
	}

	//TODO join exc_assignment ¿?¿
	public function getSubmissionsByFilename(string $filename, array $assignment_types)
	{
		$query = "SELECT * FROM " . self::TABLE_NAME. " r" .
			" LEFT JOIN exc_assignment a" .
			" ON (r." . self::COL_ASS_ID . " = a.id) " .
			" WHERE r." . self::COL_FILETITLE . " = " . $this->db->quote($filename, "string");

		if (is_array($assignment_types) && count($assignment_types) > 0)
		{
			$query .= " AND " . $this->db->in("a.type", $assignment_types, false, "integer");
		}

		$result = $this->db->query($query);

		return $this->db->fetchAll($result);
	}

	/**
	 * @param $id int
	 * @param $text string
	 * @param $is_late bool
	 */
	public function updateSubmittedText(int $id, string $text, bool $is_late): void
	{
		$this->db->manipulate("UPDATE ".self::TABLE_NAME .
			" SET " . self::COL_ATEXT . " = " . $this->db->quote($text, "text") .
			", " . self::COL_TS . " = " . $this->db->quote(ilUtil::now(), "timestamp") .
			", " . self::COL_LATE . " = " . $this->db->quote($is_late, "integer") .
			" WHERE " . self::COL_RETURNED_ID . " = " . $this->db->quote($id, "integer"));
	}

	public function getSubmissionByUserIdAndFileTitle(int $user_id, string $filetitle)
	{
		$query = "SELECT " . self::COL_OBJ_ID . ", ass_id" .
			" FROM " . self::TABLE_NAME.
			" WHERE " . self::COL_USER_ID . " = " . $this->db->quote($user_id, "integer") .
			" AND " . self::COL_FILETITLE . " = " . $this->db->quote($filetitle, "text");

		$result = $this->db->query($query);

		return $this->db->fetchAll($result);
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

	public function insertFile($exercise_id, $assignment_id, $user_id, $team_id, $post_file_name, $result_fullname, $result_mimetype, $is_late)
	{
		$next_id = $this->db->nextId(self::TABLE_NAME);

		$query = sprintf("INSERT INTO " . self::TABLE_NAME .
			" (" . self::COL_RETURNED_ID . ", " .
			self::COL_OBJ_ID . ", " .
			self::COL_USER_ID . ", " .
			self::COL_FILENAME . ", " .
			self::COL_FILETITLE . ", " .
			self::COL_MIMETYPE . ", " .
			self::COL_TS . ", " .
			self::COL_ASS_ID . ", ".
			self::COL_LATE . ", " .
			self::COL_TEAM_ID . ") " .
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
			" (" . self::COL_RETURNED_ID . ", " .
			self::COL_OBJ_ID . ", " .
			self::COL_USER_ID . ", " .
			self::COL_FILETITLE . ", " .
			self::COL_ASS_ID . ", " .
			self::COL_TS . ", " .
			self::COL_ATEXT . ", " .
			self::COL_LATE . ", " .
			self::COL_TEAM_ID . ")" .
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