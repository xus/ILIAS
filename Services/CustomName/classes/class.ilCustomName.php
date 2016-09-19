<?php

class ilCustomName {

    protected $id = 0;
    protected $name = "";

    function __construct($id = 0)
    {
        if($id)
        {
            $this->setCustomName($id);
        }
    }

    /**
     * Set object id
     * @param	integer	$a_id
     */
    function setId($a_id)
    {
        $this->id = (int)$a_id;
    }

    /**
     * Get object id
     * @return
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set object name
     * @param	string	$a_name
     */
    function setName($a_name)
    {
        $this->name = $a_name;
    }

    /**
     * Get object name
     * @return
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Save a new entry
     * @return bool
     */
    public function save()
    {
        global $ilDB;

        //error control here!
        $this->setId($ilDB->nextId('srv_cname_data'));
        $query = 'INSERT INTO srv_cname_data (id,name) ' .
            "VALUES ( " .
            $ilDB->quote($this->getId(), 'integer') . ', ' .
            $ilDB->quote($this->getName(), 'text') . ' ' .
            ") ";
        $ilDB->manipulate($query);

        return true;
    }

    /**
     * Delete object
     * @return bool
     */
    public function delete()
    {
        global $ilDB;

        //error control here!
        $query = 'DELETE FROM srv_cname_data'.
            " WHERE id =".$ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        return true;
    }

    /**
     * Edit customName
     *
     * @return bool
     */
     public function update()
     {
         global $ilDB;

         if($this->getId())
         {
             $sql = "UPDATE srv_cname_data SET".
                 " name = ". $ilDB->quote($this->getName(), 'text') .
                 " WHERE id = ". $ilDB->quote($this->getId(), 'integer');
             if($ilDB->manipulate($sql))
             {
                 return true;
             }
         }

         return false;

     }

    /**
     * Get some data for the current user
     * @return array
     */
    function getDataFromCurrentUser()
    {
        global $ilUser;
        $data = array(
            'id' => $ilUser->id,
            'city' => $ilUser->city,
            'country' => $ilUser->country,
            'title' => $ilUser->title,
            'type' => $ilUser->type
        );

        return $data;
    }

    /**
     * Get list of  objects
     * @return	array
     */
    static function getCustomNameList($filter = array())
    {

        global $ilDB;

        $sql = "SELECT id,name FROM srv_cname_data ORDER BY id";

        $set = $ilDB->query($sql);

        $res = array();
        while($row = $ilDB->fetchAssoc($set))
        {
            $res[] = $row;
        }
        return $res;

    }

    /**
     * Set full customName object
     * @param integer $cname_id
     */
    protected function setCustomName($cname_id)
    {

        global $ilDB;

        $sql = "SELECT id,name FROM srv_cname_data ".
            " WHERE id = ". $ilDB->quote($cname_id, "integer");

        $set = $ilDB->query($sql);

        if($ilDB->numRows($set))
        {
            $row = $ilDB->fetchAssoc($set);
            $this->setId($row['id']);
            $this->setName($row['name']);
        }

    }

}