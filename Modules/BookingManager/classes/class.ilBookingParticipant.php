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
		if(!ilObjUser::_exists($a_user_id) || !ilObjBookingPool::_exists($a_booking_pool_id)) {
			return false;
		}

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
		$query = 'SELECT participant_id FROM booking_member'.
			' WHERE user_id = '.$this->db->quote($this->participant_id, 'integer').
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
		$next_id = $this->db->nextId('booking_member');

		$query = 'INSERT INTO booking_member'.
			' (participant_id, user_id, booking_pool_id, assigner_user_id)'.
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

	static function getAssignableParticipants($a_bp_object_id)
	{
		global $DIC;

		$ilDB = $DIC->database();

		$res = array();

		/*$query = 'SELECT DISTINCT bm.user_id, br.object_id'.
			' FROM booking_member bm, booking_reservation br'.
			' WHERE bm.user_id NOT IN ('.
				'SELECT user_id FROM booking_reservation'.
				' WHERE object_id = '.$ilDB->quote($a_bp_object_id, 'integer').
				' AND (status IS NULL OR status <> '.ilBookingReservation::STATUS_CANCELLED.'))'.
			' AND br.object_id = '.$ilDB->quote($a_bp_object_id, 'integer');*/
		$query = 'SELECT DISTINCT bm.user_id'.
			' FROM booking_member bm'.
			' WHERE bm.user_id NOT IN ('.
				'SELECT user_id'.
				' FROM booking_reservation'.
				' WHERE object_id = '.$ilDB->quote($a_bp_object_id, 'integer').
				' AND (status IS NULL OR status <> '.ilBookingReservation::STATUS_CANCELLED.'))';

		$set = $ilDB->query($query);

		while($row = $ilDB->fetchAssoc($set))
		{
			$user_name = ilObjUser::_lookupName($row['user_id']);
			$name = $user_name['lastname'] . ", " . $user_name['firstname'];
			$index = $a_bp_object_id."_".$row['user_id'];

			$booking_object = new ilBookingObject($row['object_id']);

			if(!isset($res[$index])) {
				$res[$index] = array(
					"user_id" => $row['user_id'],
					"object_title" => array($booking_object->getTitle()),
					"name" => $name
				);
			}
			else {
				if(!in_array($booking_object->getTitle(), $res[$index]['object_title'])) {
					array_push($res[$index]['object_title'], $booking_object->getTitle());
				}
			}
		}

		return $res;
	}

	/**
	 * @param $a_booking_pool
	 * @param array|null $a_filter
	 * @return array
	 */
	static function getList($a_booking_pool, array $a_filter = null)
	{
		global $DIC;

		$ilDB = $DIC->database();
		$lng = $DIC->language();
		$ctrl = $DIC->ctrl();

		$res = array();
		$bp_object_id = 0;

		$query = 'SELECT bm.user_id, bm.booking_pool_id, br.object_id, bo.title, br.status'.
			' FROM booking_member bm'.
			' LEFT JOIN booking_reservation br ON (bm.user_id = br.user_id)'.
			' LEFT JOIN booking_object bo ON (br.object_id = bo.booking_object_id AND bo.pool_id = '.$ilDB->quote($a_booking_pool, 'integer').')';

		$where = array('bm.booking_pool_id ='.$ilDB->quote($a_booking_pool, 'integer'));
		if($filter["object"])
		{
			$bp_object_id = $filter["object"];
			$where[] = 'br.object_id = '.$ilDB->quote($bp_object_id, 'integer');
		}
		if($a_filter['title'])
		{
			$where[] = '('.$ilDB->like('title', 'text', '%'.$a_filter['title'].'%').
				' OR '.$ilDB->like('description', 'text', '%'.$a_filter['title'].'%').')';
		}
		if($a_filter['user_id'])
		{
			$where[] = 'bm.user_id = '.$ilDB->quote($a_filter['user_id'], 'integer');
		}

		$query .= ' WHERE '.implode(' AND ', $where);

		$set = $ilDB->query($query);

		while($row = $ilDB->fetchAssoc($set))
		{
			$user_name = ilObjUser::_lookupName($row['user_id']);
			$name = $user_name['lastname'].", ".$user_name['firstname'];
			$index = $a_booking_pool."_".$row['user_id'];
			$actions = array();

			if(!isset($res[$index]))
			{
				$ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', $row['user_id']);
				$ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $row['object_id']);
				if($bp_object_id && $row['status'] !=  ilBookingReservation::STATUS_CANCELLED){
					$actions[] = array(
						'text' => $lng->txt("book_deassign"),
						'url' => $ctrl->getLinkTargetByClass("ilbookingobjectgui", 'rsvConfirmCancelUser')
					);
				}
				$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
				$ctrl->setParameterByClass('ilbookingparticipantgui', 'object_id', '');

				$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', $row['user_id']);
				$actions[] = array(
					'text' => $lng->txt("book_assign_object"),
					'url' => $ctrl->getLinkTargetByClass("ilbookingparticipantgui", 'assignObjects')
				);
				$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');

				$res[$index] = array(
					"object_title" => array(),
					"name" => $name,
					"actions" => $actions
				);
				if($row['status'] !=  ilBookingReservation::STATUS_CANCELLED) {
					$res[$index]['object_title'] = array($row['title']);
				}
			} else {
				if(!in_array($row['title'], $res[$index]['object_title']) && $row['status'] !=  ilBookingReservation::STATUS_CANCELLED) {
					array_push($res[$index]['object_title'], $row['title']);
				}
			}
		}
		return $res;
	}

	/**
	 * @param $a_booking_pool_id
	 * @return array
	 */
	static function getBookingPoolParticipants(integer $a_booking_pool_id) : array
	{
		global $DIC;
		$ilDB = $DIC->database();
		$sql = 'SELECT * FROM booking_member WHERE booking_pool_id = '.$ilDB->quote($a_booking_pool_id, 'integer');

		$set = $ilDB->query($sql);

		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row['user_id'];
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
			" RIGHT JOIN booking_member m ON (ud.usr_id = m.user_id)".
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

	protected function isParticipantAssigned($a_booking_object_id, $a_participant_id)
	{
		if(!empty(ilBookingReservation::getObjectReservationForUser($a_booking_object_id, $a_participant_id))){
			return true;
		} else {
			return false;
		}
	}
}