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
		$this->il_user = $DIC->user();

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

		$assigner_id = $this->il_user->getId();
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

	static function getList($a_booking_pool_id, array $a_object_ids = null, array $a_filter = null)
	{
		global $DIC;
		$ilDB = $DIC->database();

		$res = array();
		$where = array();

		$query = 'SELECT bm.*, bo.title FROM il_booking_member bm'.
			' JOIN booking_reservation br ON (bm.usr_id = br.user_id)'.
			' JOIN booking_object bo ON (br.object_id = bo.booking_object_id)';

		$where[] = 'booking_pool_id ='.$ilDB->quote($a_booking_pool_id, 'integer');

		if($a_filter['user_id'])
		{
			$where[] = 'usr_id ='.$ilDB->quote($a_filter['user_id']);
		}

		if($a_filter['title'])
		{
			$where[] = '('.$ilDB->like('title', 'text', '%'.$a_filter['title'].'%').
				' OR '.$ilDB->like('description', 'text', '%'.$a_filter['title'].'%').')';
		}

		$query .= ' WHERE '.implode(' AND ', $where);

		$set = $ilDB->query($query);
		//TODO change the dummy obj_id
		while($row = $ilDB->fetchAssoc($set))
		{
			$obj_id = "DUMMY OBJECT NAME";
			$pool_id = $row['booking_pool_id'];
			$usr_id = $row['usr_id'];
			$index = $pool_id."_".$usr_id;

			if(!isset($res[$index]))
			{
				$user_name = ilObjUser::_lookupName($row['usr_id']);
				$name = $user_name['lastname'].", ".$user_name['firstname'];

				//TODO add the action
				$res[$index] = array(
					"object_id" => $obj_id,
					"name" => $name,
					"actions" => "DUMMY ACTION TITLE"
				);
			}

		}

		return $res;
	}

	/**
	 * Get all users who are participants in this booking pool.
	 *
	 * @param integer $a_pool_id
	 * @return array
	 */
	public static function getUserFilter($a_pool_id)
	{
		global $DIC;

		$ilDB = $DIC->database();

		$res = array();

		$sql = "SELECT ud.usr_id,ud.lastname,ud.firstname,ud.login".
			" FROM usr_data ud ".
			" RIGHT JOIN il_booking_member m ON (ud.usr_id = m.usr_id)".
			" WHERE ud.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer").
			" AND m.booking_pool_id = ".$ilDB->quote($a_pool_id, "integer").
			" ORDER BY ud.lastname,ud.firstname";

		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["usr_id"]] = $row["lastname"].", ".$row["firstname"].
				" (".$row["login"].")";
		}

		return $res;
	}
}