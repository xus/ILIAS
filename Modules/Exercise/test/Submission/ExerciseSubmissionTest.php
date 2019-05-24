<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ExerciseSubmissionTest extends TestCase
{
	const USER_ID = 6;
	/**
	 * @var ilExAssignment
	 */
	protected $assignment;

	public function setUp(): void
	{
		require_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$this->assignment = new ilExAssignment();
	}

	// The following Tests are for individuals not for teams.

	/**
	 * submission created with minimum amount of arguments
	 */
	public function testCreate()
	{
		$submission = new ilExSubmission($this->assignment, self::USER_ID);

		$this->assertEquals($submission->getUserId(), self::USER_ID);
		$this->assertEquals($submission->getAssignment(), $this->assignment);
	}

}