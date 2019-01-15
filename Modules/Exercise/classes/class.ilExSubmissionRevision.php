<?php
/* Copyright (c) 2018 Extended GPL, see docs/LICENSE */

/**
 * Revision Handler for Exercise Submissions.
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */

class ilExSubmissionRevision
{
	/**
	 * @var ilExSubmission
	 */
	protected $submission;

	/**
	 * @var int
	 */
	protected $ass_id;

	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var ilDBInterface
	 */
	protected $db;


	/**
	 * ilExSubmissionRevision constructor.
	 * @param ilExSubmission $a_submission
	 */
	public function __construct(ilExSubmission $a_submission)
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->submission = $a_submission;
		$this->ass_id = $this->submission->getAssignment()->getId();
		$this->usr_id = $this->submission->getUserId();
	}


	/**
	 * Store the submission version in the DB.
	 * The user will be allowed to submit again.
	 * @return int
	 */
	public function setVersion() : int
	{
		$next_version = $this->getLastVersionNumber() + 1;

		$submissions = $this->submission->getSubmissionsByUser();

		foreach($submissions as $submission)
		{
			$ass_mem_status = new ilExAssignmentMemberStatus($this->ass_id, $this->submission->getUserId());

			$next_id = $this->db->nextId('exc_submission_version');

			$affectedRows = $this->db->manipulateF(
				"INSERT INTO exc_submission_version (id, returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, atext, late, team_id, status, status_time, mark, u_comment, version, versioned)".
				" VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'integer', 'text', 'text', 'text', 'timestamp', 'integer', 'text', 'integer', 'integer', 'text', 'timestamp', 'text', 'text',' integer', 'timestamp'),
				array(
					$next_id,
					$submission['returned_id'],
					$submission['obj_id'],
					$submission['user_id'],
					$submission['filename'],
					$submission['filetitle'],
					$submission['mimetype'],
					$submission['ts'],
					$submission['ass_id'],
					$submission['atext'],
					$submission['late'],
					$submission['team_id'],
					$ass_mem_status->getStatus(),
					$ass_mem_status->getStatusTime(),
					$ass_mem_status->getMark(),
					$ass_mem_status->getComment(),
					$next_version,
					ilUtil::now()
				)
			);
		}
		return $next_version;
	}


	/**
	 * Get Last submission version number
	 * @return integer
	 */
	public function getLastVersionNumber() : int
	{
		$sql = "SELECT max(version) version".
			" FROM exc_submission_version".
			" WHERE ass_id = ".
			$this->db->quote($this->ass_id, "integer").
			" AND user_id = ".
			$this->db->quote($this->usr_id, "integer");

		$res = $this->db->query($sql);
		$row = $this->db->fetchAssoc($res);

		return (int)$row['version'];
	}


	/**
	 * Compare the submission with the last revision to determine if it was versioned or not.
	 * @return bool
	 */
	public function isVersioned() : bool
	{
		$sql = "SELECT count(r.returned_id) count".
			" FROM exc_returned r, exc_submission_version v".
			" WHERE r.obj_id = ".$this->submission->getAssignment()->getExerciseId().
			" AND r.ass_id = ".$this->ass_id.
			" AND r.user_id = ".$this->usr_id.
			" AND r.obj_id = v.obj_id".
			" AND r.ass_id = v.ass_id".
			" AND r.user_id = v.user_id".
			" AND r.ts = v.ts";

		$res = $this->db->query($sql);
		$row = $this->db->fetchAssoc($res);

		return (bool)$row['count'];
	}

	/**
	 * Get all versions for the submission.
	 * @return array
	 */
	public function getRevisions() : array
	{
		$sql = "SELECT * FROM exc_submission_version".
			" WHERE obj_id = ".$this->submission->getAssignment()->getExerciseId().
			" AND ass_id = ".$this->ass_id.
			" AND user_id = ".$this->usr_id.
			" ORDER BY version DESC";

		$res = $this->db->query($sql);
		while($row = $this->db->fetchAssoc($res))
		{
			$versions[] = $row;
		}
		return $versions ? $versions : array();
	}

	public function sendNotification()
	{
		$get_ref = (int)$_GET['ref_id'];
		$exc_id = $this->submission->getAssignment()->getExerciseId();

		if(in_array($get_ref, ilObjExercise::_getAllReferences($exc_id)))
		{
			$not = new ilExerciseMailNotification();
			$not->setType(ilExerciseMailNotification::TYPE_SUBMISSION_VERSIONED);
			$not->setAssignmentId($this->ass_id);
			$not->setRefId($get_ref);
			$not->setRecipients(array($this->submission->getUserId()));
			$not->send();
		}
	}
}