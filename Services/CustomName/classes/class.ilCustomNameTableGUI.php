<?php
include_once("Services/Table/classes/class.ilTable2GUI.php");

class ilCustomNameTableGUI extends ilTable2GUI
{
    function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $ilCtrl;

        $this->setId('my_id');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle("LIST OF CUSTOM NAMES");
        $this->addColumn("Id", "", "10%");
        $this->addColumn("Name", "", "90%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_row_custom_name_template.html", "Services/CustomName");

        $this->getCustomNameData();

    }

    /**
     * Put data into array
     */
    protected function getCustomNameData()
    {
        include_once("./Services/CustomName/classes/class.ilCustomName.php");

        $cname = new ilCustomName();

        $data = $cname->getCustomNameList();

        $this->setData($data);
    }

    /**
     * Fill a single data row
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("TXT_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
    }
}