<?php

class ilCustomName {

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
     * Get list of  objects for given type ( WRONG WAY // use the object.)
     * @return	array
     */
    /*
    static function getUserList()
    {
        global $ilDB;

        $sql = "SELECT firstname,lastname FROM usr_data ORDER BY firstname";

        $set = $ilDB->query($sql);
        $res = array();
        while($row = $ilDB->fetchAssoc($set))
        {
            $res[] = $row;
        }
        return $set;

    }
    */

}