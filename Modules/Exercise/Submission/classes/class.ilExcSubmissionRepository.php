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

	//TODO: ?? remove all these column constants.
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
	public function getById(int $submission_id) : ?ilExcSubmissionData
	{
		$this->db->setLimit(1, 0);

		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_RETURNED_ID . " = " . $this->db->quote($submission_id, "integer");

		return $this->getDataObjectFromQuery($query);
	}

	/**
	 * @inheritdoc
	 */
	public function hasSubmissions(int $assignment_id) : int
	{
		$this->db->setLimit(1, 0);

		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($assignment_id, "integer") .
			" AND (" . self::COL_FILENAME . " IS NOT NULL OR " . self::COL_ATEXT ." IS NOT NULL)" .
			" AND " . self::COL_TS . " IS NOT NULL";

		$res = $this->db->query($query);

		return $res->numRows();
	}

	/**
	 * Update web_dir_access_time. It defines last HTML opening data.
	 * @param int $assignment_id
	 * @param int $member_id
	 */
	public function updateWebDirAccessTime(int $assignment_id, int $member_id) : void
	{
		$this->db->manipulate("UPDATE " . self::TABLE_NAME .
			" SET " . self::COL_WEB_DIR_ACCESS_TIME . " = " . $this->db->quote(ilUtil::now(), "timestamp") .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_USER_ID . " = " . $this->db->quote($member_id, "integer"));
	}

	/**
	 * @param int $a_ass_id
	 * @return array
	 */
	public function getAllByAssignmentId(int $a_ass_id) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($a_ass_id, "integer");

		return $this->getArrayOfDataObjectsFromQuery($query);
	}

	public function getAllByAssignmentIdAndTeamId(int $assignment_id, int $team_id) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_TEAM_ID . " = " .
			$this->db->quote($team_id, "integer");

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	public function getTeamSubmissionsBySubmissionsIdAndTimestamp(int $assignment_id, int $team_id, array $submission_ids, int $min_timestamp) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_TEAM_ID . " = " .
			$this->db->quote($team_id, "integer");

		if($submission_ids) {
			$sql .= " AND ".$this->db->in("returned_id", $submission_ids, false, "integer");
		}

		if($min_timestamp) {
			$sql .= " AND ts > ".$this->db->quote($min_timestamp, "timestamp");
		}

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	//TODO if submissio_is is null instead aof array
	public function getUsersSubmissionsBySubmissionsIdAndTimestamp(int $assignment_id, array $user_ids, array $submission_ids, int $min_timestamp) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer");

		if($submission_ids) {
			$query .= " AND ".$this->db->in("returned_id", $submission_ids, false, "integer");
		}

		if($min_timestamp) {
			$query .= " AND ts > ".$this->db->quote($min_timestamp, "timestamp");
		}

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	//TODO: Why we are checking if its a team or a bunch of users when we have the submissions who are PK in the DB (getTeamSubmissionsByIds, and getUsersSubmissionsByIds)
	public function getTeamSubmissionsByIds(int $team_id, array $submission_ids) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_TEAM_ID . " = " . $this->db->quote($team_id, "integer") .
			" AND " . $this->db->in(self::COL_RETURNED_ID, $submission_ids, false, "integer");

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	//read TODO from getTeamSubmissionsByIds
	public function getUsersSubmissionsByIds(array $users_ids, array $submission_ids) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . $this->db->in(self::COL_USER_ID, $users_ids, false, "integer") .
			" AND " . $this->db->in(self::COL_RETURNED_ID, $submission_ids, false, "integer");

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	/**
	 * @param int $assignment_id
	 * @param array $user_ids
	 * @return array
	 */
	public function getAllByUserIds(int $assignment_id, array $user_ids) : array
	{
		$query = "SELECT * FROM ".self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " .
			$this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer");

		return $this->getArrayOfDataObjectsFromQuery($query);

	}

	/**
	 * @param int $exercise_id
	 * @param int $assignment_id
	 * @return array
	 */
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

	/**
	 * @param string $filename
	 * @param array $assignment_types
	 * @return array
	 */
	public function getSubmissionsByFilename(string $filename, array $assignment_types) : array
	{
		$query = "SELECT * FROM " . self::TABLE_NAME. " r" .
			" LEFT JOIN exc_assignment a" .
			" ON (r." . self::COL_ASS_ID . " = a.id) " .
			" WHERE r." . self::COL_FILETITLE . " = " . $this->db->quote($filename, "string");

		if (is_array($assignment_types) && count($assignment_types) > 0)
		{
			$query .= " AND " . $this->db->in("a.type", $assignment_types, false, "integer");
		}

		return $this->getArrayOfDataObjectsFromQuery($query);
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

	/**
	 * TODO READ THIS METHOD--> select obj_id???=?
	 * THIS AFFECTS checkExercise of ilPortfolioExerciseGUI and ilBlogExerciseGUI
	 * @param int $user_id
	 * @param string $filetitle
	 * @return array
	 */
	public function getSubmissionByUserIdAndFileTitle(int $user_id, string $filetitle) : array
	{
		$query = "SELECT *" .
			" FROM " . self::TABLE_NAME.
			" WHERE " . self::COL_USER_ID . " = " . $this->db->quote($user_id, "integer") .
			" AND " . self::COL_FILETITLE . " = " . $this->db->quote($filetitle, "text");

		return $this->getDataObjectFromQuery($query);
	}

	/**
	 * @param int $assignment_id
	 * @param array $user_ids
	 * @param int $tutor_id
	 * @return string
	 */
	public function getLastDownloadTime(int $assignment_id, array $user_ids, int $tutor_id) : string
	{
		$query = "SELECT download_time FROM exc_usr_tutor" .
 			" WHERE  ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in("usr_id", $user_ids, "", "integer") .
			" AND tutor_id = " . $this->db->quote($tutor_id, "integer");

		$lu_set = $this->db->query($query);
		$lu_rec = $this->db->fetchAssoc($lu_set);

		return $lu_rec["download_time"];
	}

	/**
	 * Insert for submission type upload in the database
	 * @param ilExcSubmissionData $submission_data
	 * @throws ilFileUtilsException
	 */
	public function insertFile(ilExcSubmissionData $submission_data) : void
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
			$this->db->quote($submission_data->getExerciseId(), "integer"),
			$this->db->quote($submission_data->getUserId(), "integer"),
			$this->db->quote($submission_data->getDeliverResultFullname(), "text"),
			$this->db->quote(ilFileUtils::getValidFilename($submission_data->getFileName()), "text"),
			$this->db->quote($submission_data->getDeliverResultMymetype(), "text"),
			$this->db->quote(ilUtil::now(), "timestamp"),
			$this->db->quote($submission_data->getAssignmentId(), "integer"),
			$this->db->quote($submission_data->isLate(), "integer"),
			$this->db->quote($submission_data->getTeamId(), "integer")
		);

		$this->db->manipulate($query);
	}

	/**
	 * TODO use databoject here
	 * TODO read if we can use the json serializer for this mater
	 * Insert submission in the database
	 * @param array $data
	 * @return int
	 */
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

	/**
	 * TODO submission id is unique in the db... why check by exercise id, assignment_id? exc_returned.returned_id
	 * Delete one submission for an specific user and assignment.
	 * @param int $exercise_id
	 * @param int $user_id
	 * @param int $assignment_id
	 * @param int $submission_id
	 */
	public function deleteUserSubmission(int $exercise_id, int $user_id, int $assignment_id, int $submission_id) : void
	{
		$this->db->manipulate("DELETE FROM " . self::TABLE_NAME .
			" WHERE obj_id = " . $this->db->quote($exercise_id, "integer") .
			" AND user_id = " . $this->db->quote($user_id, "integer") .
			" AND ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND returned_id = " . $this->db->quote($submission_id, "integer")
		);
	}

	/**
	 * TODO submission id is unique in the db... why check by exercise id, assignment_id? exc_returned.returned_id
	 * Delete one submission for an specific team and assignment.
	 * @param int $exercise_id
	 * @param int $team_id
	 * @param int $assignment_id
	 * @param int $submission_id
	 */
	public function deleteTeamSubmission(int $exercise_id, int $team_id, int $assignment_id, int $submission_id) : void
	{
		$this->db->manipulate("DELETE FROM " . self::TABLE_NAME .
			" WHERE obj_id = " . $this->db->quote($exercise_id, "integer") .
			" AND team_id = " . $this->db->quote($team_id, "integer") .
			" AND ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND returned_id = " . $this->db->quote($submission_id, "integer")
		);
	}

	public function deleteByTeamAndIds(int $team_id, array $submission_ids) : void
	{
		$this->db->manipulate("DELETE FROM " . self::TABLE_NAME .
			" WHERE team_id = " . $this->db->quote($team_id, "integer") .
			" AND " . $this->db->in("returned_id", $submission_ids, false, "integer")
		);
	}

	public function deleteByUsersAndIds(array $user_ids, array $submission_ids) : void
	{
		$this->db->manipulate("DELETE FROM " . self::TABLE_NAME .
			" WHERE " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer") .
			" AND " . $this->db->in(self::COL_RETURNED_ID, $submission_ids, false, "integer")
		);
	}

	public function getLastWebDirectoryAccessByTeam(int $assignment_id, int $team_id) : array
	{
		$this->db->setLimit(1, 0);

		$q = "SELECT web_dir_access_time FROM " . self::TABLE_NAME .
			" WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND (filename IS NOT NULL OR atext IS NOT NULL)" .
			" AND web_dir_access_time IS NOT NULL" .
			" AND " . self::COL_TEAM_ID . " = " . $this->db->quote($team_id, "integer") .
			" ORDER BY web_dir_access_time DESC";

		$res = $this->db->query($q);

		return $this->db->fetchAssoc($res);
	}

	//THIS METHOD CAN BE REMOVED TODO: figure out
	public function getLastWebDirectoryAccessByUsers(int $assignment_id, array $user_ids) : array
	{
		$this->db->setLimit(1, 0);

		$q = "SELECT web_dir_access_time FROM " . self::TABLE_NAME .
			" WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
			" AND (filename IS NOT NULL OR atext IS NOT NULL)" .
			" AND web_dir_access_time IS NOT NULL" .
			" AND " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer") .
			" ORDER BY web_dir_access_time DESC";

		$res = $this->db->query($q);

		return $this->db->fetchAssoc($res);
	}

	/**
	 * @param int $assignment_id
	 * @param int $team_id
	 * @return array
	 */
	public function getLastSubmissionByTeam(int $assignment_id, int $team_id) : ?ilExcSubmissionData
	{
		$this->db->setLimit(1, 0);

		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($assignment_id, "integer") .
			" AND " . self::COL_TEAM_ID . " = " . $team_id .
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND ts IS NOT NULL".
			" ORDER BY ts DESC";

		return $this->getDataObjectFromQuery($query);
	}

	/**
	 * @param int $assignment_id
	 * @param array $user_ids
	 * @return null | ilExcSubmissionData
	 */
	public function getLastSubmissionByUsers(int $assignment_id, array $user_ids) : ?ilExcSubmissionData
	{
		$this->db->setLimit(1, 0);

		$query = "SELECT * FROM " . self::TABLE_NAME .
			" WHERE " . self::COL_ASS_ID . " = " . $this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer") .
			" AND (filename IS NOT NULL OR atext IS NOT NULL)" .
			" AND ts IS NOT NULL" .
			" ORDER BY ts DESC";

		return $this->getDataObjectFromQuery($query);
	}

	public function getTeamSubmissionIdsByTutorId(int $assignment_id, int $team_id, int $tutor_id) : array
	{
		$q = "SELECT exc_returned.returned_id AS id ".
			"FROM exc_usr_tutor, exc_returned ".
			"WHERE exc_returned.ass_id = exc_usr_tutor.ass_id ".
			" AND exc_returned.user_id = exc_usr_tutor.usr_id ".
			" AND exc_returned.ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND " . self::COL_TEAM_ID . " = " . $team_id .
			" AND exc_usr_tutor.tutor_id = ".$this->db->quote($tutor_id, "integer").
			" AND exc_usr_tutor.download_time < exc_returned.ts ";

		$new_up_set = $this->db->query($q);

		return $this->db->fetchAssoc($new_up_set);
	}

	/**
	 * TODO FIX THIS METHOD and rename it getIdsByUserIdsAndTutorId
	 * @param int $assignment_id
	 * @param array $user_ids
	 * @param int $tutor_id
	 * @return null | array
	 */
	public function getUsersSubmissionIdsByTutorId(int $assignment_id, array $user_ids, int $tutor_id) : ?array
	{
		$q = "SELECT exc_returned.returned_id AS id" .
			" FROM exc_usr_tutor, exc_returned" .
			" WHERE exc_returned.ass_id = exc_usr_tutor.ass_id" .
			" AND exc_returned.user_id = exc_usr_tutor.usr_id" .
			" AND exc_returned.ass_id = ".$this->db->quote($assignment_id, "integer") .
			" AND " . $this->db->in(self::COL_USER_ID, $user_ids, false, "integer") .
			" AND exc_usr_tutor.tutor_id = ".$this->db->quote($tutor_id, "integer") .
			" AND exc_usr_tutor.download_time < exc_returned.ts";

		$new_up_set = $this->db->query($q);

		return $this->db->fetchAssoc($new_up_set);
	}

	// Utils. Maybe these methods can be extracted from here to be used in other repos

	/**
	 * Mapping db rows with object data to centralize future changes.
	 * @param string $query
	 * @return ilExcSubmissionData
	 */
	function getDataObjectFromQuery(string $query) : ?ilExcSubmissionData
	{
		$result = $this->db->query($query);

		while($row = $this->db->fetchAll($result))
		{
			return $this->mapArrayToExcSubmissionData($row);
		}

		return null;
	}

	public function getArrayOfDataObjectsFromQuery(string $query) : array
	{
		$result = $this->db->query($query);

		$submissions = array();
		while($row = $this->db->fetchAll($result))
		{
			$submissions[] = $this->mapArrayToExcSubmissionData($row);
		}

		return $submissions;
	}

	//TODO smell code here.
	public function mapArrayToExcSubmissionData(array $array_data) : ilExcSubmissionData
	{
		$data = current($array_data);

		$submission = new ilExcSubmissionData(
			$data['obj_id'],
			$data['ass_id'],
			$data['user_id'],
			$data['team_id'],
			$data['filetitle'],
			$data['filename'],
			$data['mimetype'],
			$data['late'],
			$data['timestamp']
		);

		return $submission;
	}
}