<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once("/Users/xus/Sites/ILIAS/Modules/Exercise/Submission/classes/class.ilExcSubmissionRepository.php");
require_once("/Users/xus/Sites/ILIAS/Modules/Exercise/Submission/classes/class.ilExcSubmissionRepositoryInterface.php");

class ExerciseSubmissionRepositoryTest extends TestCase
{
	const TEAM_ID_ZERO = 0;
	const EXC_ID = 1;
	const ASS_ID = 2;
	const TEAM_ID = 3;
	const USER_ID = 6;
	const IS_LATE = false;

	/**
	 * @var ilExAssignment
	 */
	protected $assignment;

	protected $user_object;

	public function setUp(): void
	{
		//TODO remove this
		$this->assignment = $this->getMockBuilder('ilExAssignment')
			->disableOriginalConstructor()
			->getMock();
	}

	// The following Tests are for individuals not for teams.

	/**
	 * submission created with minimum amount of arguments
	 */
	public function testCreate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$interface = $this->getMockBuilder('ilExcSubmissionInterface')
			->disableOriginalConstructor()
			->getMock();

		$repository = new ilExcSubmissionRepositorys($database);

		$repository->insertFile(
			self::EXC_ID,
			self::ASS_ID,
			self::USER_ID,
			self::TEAM_ID_ZERO,
			"dummy_string",
			"dummy_string",
			"dummy_string",
			self::IS_LATE
		);
		$submission = new ilExSubmission($this->assignment, self::USER_ID);

		$this->assertEquals("aaaa", self::USER_ID);
		$this->assertEquals($submission->getAssignment(), $this->assignment);
	}




}