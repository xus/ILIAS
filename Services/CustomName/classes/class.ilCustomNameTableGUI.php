<?php
include_once("Services/Table/classes/class.ilTable2GUI.php");

class ilCustomNameTableGUI extends ilTable2GUI
{
    function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $ilCtrl;

        $this->setId('my_id');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initFilter();
        //force to show the filter input text,
        $this->setDisableFilterHiding(true);

        $this->setTitle("LIST OF CUSTOM NAMES");
        $this->setEnableHeader(true);

        /**
         *  CONTINUE HERE
         */
        $this->addColumn("", "", "5%", "", true, "");

        $this->addColumn("Id", "", "5%");
        $this->addColumn("Name", "", "90%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_row_custom_name_template.html", "Services/CustomName");

        $this->addMultiCommand("deleteRecords", "Delete");
        $this->addMultiCommand("addCustomNameAction", "Add");

        $this->addCommandButton("deleteRecords","Delete command button","","", "");
        $this->setSelectAllCheckbox("row_id"); // #16472 (This bug number in mantis doesn't seem have any relation)

        $this->getCustomNameData();
    }

    /**
     * Put data into array
     */
    protected function getCustomNameData()
    {
        include_once("./Services/CustomName/classes/class.ilCustomName.php");

        $cname = new ilCustomName();

        //$data = $cname->getCustomNameList($this->filter['name']); //BETTER PASS ALL THE FILTER
        $data = $cname->getCustomNameList($this->filter);

        $this->setData($data);
    }

    /**
     * Fill a single data row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;
        //include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $this->tpl->setVariable("ROW_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);

        /**
         * ACTIONS (edit,delete)
         */
        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $actions = new ilAdvancedSelectionListGUI();
        $actions-> setListTitle($lng->txt("actions"));

        $ilCtrl->setParameter($this->getParentObject(), "myid", $a_set["id"]);
        $link = $ilCtrl->getLinkTarget($this->getParentObject(), "editCustomName");
        $actions->addItem($lng->txt("edit"), "", $link);

        $this->tpl->setVariable("ACTIONS", $actions->getHTML());

    }

    /**
     * Set up the filters
     */
    public function initFilter()
    {
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");

        $ti = new ilTextInputGUI("Filter by Name", "name");

        $ti->setMaxLength(20);
        /**
         * Where is this setSize used?
         */
        $ti->setSize(500);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter['name'] = $ti->getValue();
    }
}