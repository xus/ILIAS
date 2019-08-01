<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * [WIP] This class is on development status.
 * Submission data object
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionData implements JsonSerializable {

	/**
	 * @var int
	 */
	private $exercise_id;

	/**
	 * @var int
	 */
	private $assignment_id;

	/**
	 * @var int
	 */
	private $user_id;

	/**
	 * @var int
	 */
	private $team_id;

	/**
	 * @var string
	 */
	private $file_name;

	/**
	 * @var string
	 */
	private $deliver_result_fullname;

	/**
	 * @var string
	 */
	private $deliver_result_mymetype;

	/**
	 * @var int
	 */
	private $is_late;


	/**
	 * TODO refactor: too many arguments
	 * ilExcSubmissionData constructor.
	 * @param int $exercise_id
	 * @param int $assignment_id
	 * @param int $user_id
	 * @param int $team_id
	 * @param string $file_name
	 * @param string $deliver_result_fullname
	 * @param string $deliver_result_mymetype
	 * @param int $is_late
	 */
	public function __construct(int $exercise_id, int $assignment_id, int $user_id, int $team_id, string $file_name, string $deliver_result_fullname, string $deliver_result_mymetype, int $is_late)
	{
		$this->exercise_id= $exercise_id;
		$this->assignment_id = $assignment_id;
		$this->user_id = $user_id;
		$this->team_id = $team_id;
		$this->file_name = $file_name;
		$this->deliver_result_fullname = $deliver_result_fullname;
		$this->deliver_result_mymetype = $deliver_result_mymetype;
		$this->is_late = $is_late;
	}

	/**
	 * @return int
	 */
	public function getExerciseId(): int
	{
		return $this->exercise_id;
	}

	/**
	 * @return int
	 */
	public function getAssignmentId(): int
	{
		return $this->assignment_id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->user_id;
	}

	/**
	 * @return int
	 */
	public function getTeamId(): int
	{
		return $this->team_id;
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return $this->file_name;
	}

	/**
	 * @return string
	 */
	public function getDeliverResultFullname(): string
	{
		return $this->deliver_result_fullname;
	}

	/**
	 * @return string
	 */
	public function getDeliverResultMymetype(): string
	{
		return $this->deliver_result_mymetype;
	}

	public function isLate()
	{
		return $this->is_late;
	}

	/**
	 * TODO: This method will be useful if ever versioning/eventing are needed.
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return get_object_vars($this);
	}
}