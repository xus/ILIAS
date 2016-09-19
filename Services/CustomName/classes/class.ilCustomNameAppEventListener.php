<?php

class ilCustomNameAppEventListener
{
    static function handleEvent($a_component, $a_event, $a_parameter)
    {
        if('Services/CustomName' == $a_component && 'creation' == $a_event)
        {
            global $ilUser;
            require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemNotification.php';
            $notification = new ilBuddySystemNotification($ilUser);
            $notification->setRecipientIds(array($ilUser->getUserIdByEmail("lopez@leifos.com")));
            $notification->send();
        }
    }
}