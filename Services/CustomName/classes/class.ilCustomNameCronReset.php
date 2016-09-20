<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for testing. Delete all custom names in the DB.
 */
class ilCustomNameCronReset extends ilCronJob
{
    //ABSTRACT METHODS

    /**
     * [string] getId(): returns the Id as defined in the module.xml or service.xml
     */
    function getId()
    {
        return "cron_custom_name_reset";
    }

    /**
     * [int] getDefaultScheduleType(): see Schedule
     */
    function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * [int] getDefaultScheduleType(): see Schedule
     */
    function getDefaultScheduleValue()
    {
        return;
    }

    /**
     * [bool] hasAutoActivation(): is the cron-job active after "installation" or should it be activated manually?
     */
    function hasAutoActivation()
    {
        return true;
    }

    /**
     * [bool] hasFlexibleSchedule(): can the schedule be edited by an adminstrator or is it static?
     */
    function hasFlexibleSchedule()
    {
        return true;
    }

    public function getTitle()
    {
        global $lng;
        
        $lng->loadLanguageModule("customname");

        return $lng->txt("cname_cron_reset");
    }

    public function getDescription()
    {
        global $lng;

        $lng->loadLanguageModule("customname");

        return $lng->txt("cname_cron_reset_info");
    }

    /**
     * [ilCronJobResult] run(): process the cron-job
     */
    function run()
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;

        require_once("./Services/CustomName/classes/class.ilCustomName.php");

        $rows_deleted = ilCustomName::resetCustomNames();
        if($rows_deleted > 0)
        {
            $status = ilCronJobResult::STATUS_OK;
        }

        $result = new ilCronJobResult();
        $result->setStatus($status);

        $result->setMessage("This cron deleted: ".$rows_deleted." custom names");
        $result->setCode("#".$rows_deleted);

        return $result;

    }


}