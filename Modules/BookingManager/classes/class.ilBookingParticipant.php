<?php

class ilBookingParticipant
{
	protected $lng;
	protected $db;
	protected $participant_id;
	protected $booking_pool_id;
	protected $is_new;

	/**
	 * ilBookingParticipant constructor.
	 * @param $a_user_id integer
	 * @param $a_booking_pool_id integer
	 */
	public function __construct($a_user_id, $a_booking_pool_id)
	{
		global $DIC;
		$this->lng = $DIC->language();
		$this->db = $DIC->database();

		$this->participant_id = $a_user_id;
		$this->booking_pool_id = $a_booking_pool_id;

		// if read and not exists, store it in db.
		if(!$this->read()){
			$this->save();
			$this->is_new = true;
		} else {
			$this->is_new = false;
		}
	}

	protected function read()
	{
		$query = 'SELECT participant_id FROM il_booking_member'.
			' WHERE usr_id = '.$this->db->quote($this->participant_id, 'integer').
			' AND booking_pool_id = '.$this->db->quote($this->booking_pool_id, 'integer');

		ilLoggerFactory::getRootLogger()->debug("Query => ".$query);
		$set = $this->db->query($query);
		$row = $this->db->fetchAssoc($set);
		if(empty($row)) {
			return false;
		} else {
			return $row['participant_id'];
		}
	}

	protected function save()
	{
		global $DIC;
		$user = $DIC->user();
		$assigner_id = $user->getId();
		$next_id = $this->db->nextId('il_booking_member');

		$query = 'INSERT INTO il_booking_member'.
			' (participant_id, usr_id, booking_pool_id, assigner_user_id)'.
			' VALUES ('.$this->db->quote($next_id, 'integer').
			','.$this->db->quote($this->participant_id, 'integer').
			','.$this->db->quote($this->booking_pool_id, 'integer').
			','.$this->db->quote($assigner_id, 'integer').')';

		$this->db->manipulate($query);
	}

	public function getIsNew()
	{
		return $this->is_new;
	}

	protected function getList()
	{

	}
}