<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Custom Name GUI class
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 *
 * after making these changes you need to reload the ctrl structure
 * @     ilCtrl_Calls ilCustomNameGUI: ilCustomSecondClassGUI
 * @version $Id$
 */
class ilCustomNameGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    function __construct()
    {
        global $tpl;

        // use DIC instead

        $tpl->getStandardTemplate();
    }

    /**
     * Execute command
     *
     * @param
     * @return
     */

    function executeCommand()
    {
        global $ilCtrl, $tpl;

        // determine next class in the call structure
        $next_class = $ilCtrl->getNextClass($this);

        switch($next_class)
        {

            // process command, if current class is responsible to do so
            default:
                // determin the current command (take "view" as default)
                $cmd = $ilCtrl->getCmd("view");

                //Ok I had to add "view" to this array to be able to pass to another method in the same class!  CASE SENSITIVE!
                if (in_array($cmd, array("view", "viewForm", "createForm", "save", "viewTableList", "viewUserDataWithPanel", "applyFilter", "resetFilter", "deleteRecords")))
                {
                    $this->$cmd();
                }

                break;
        }

        $tpl->show();
    }

    /**
     * View hello world...
     *
     */
    protected function view()
    {
        global $tpl,$ilTabs;

        $this->setTabs();
        $ilTabs->activateTab("id_welcome");
        $timestamp = new ilDateTime(time(),IL_CAL_UNIX);
        $tpl->setContent("<h1>Hello!</h1><br /> this is the first view, <b>use the tabs to navigate.</b><br/><br/>Timestamp: ".$timestamp);
    }

    /**
     * Custom view with template
     *
     */
    protected function viewCustom()
    {
        global $tpl;

        $my_tpl = new ilTemplate("tpl.my_template.html", true, true, "Services/CustomName");

        $my_tpl->setCurrentBlock("my_block");
        $my_tpl->setVariable("TEXT", "This is the text");
        $my_tpl->setVariable("VALUE", "This is the value");
        $my_tpl->parseCurrentBlock();

        $tpl->setContent($my_tpl->get());

    }

    /**
     * Custom view with some user data.
     */
    protected function viewUserData()
    {
        global $tpl;

        include_once('./Services/CustomName/classes/class.ilCustomName.php');

        $cname = new ilCustomName();

        $user_data = $cname->getDataFromCurrentUser();

        $usr_tpl = new ilTemplate("tpl.user_template.html", true, true, "Services/CustomName");

        $usr_tpl->setCurrentBlock("user_block");
        /**
         * placeholders for the template with user data(ID, CITY, COUNTRY, TITLE, TYPE)
         */
        foreach ($user_data as $key => $value){
            $usr_tpl->setVariable(strtoupper($key), $value);
        }
        $usr_tpl->parseCurrentBlock();

        $tpl->setContent($usr_tpl->get());

    }

    /**
     *  Custom view with some user data USING PANEL COMPONENT
     */
    protected function viewUserDataWithPanel()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng;

        include_once('./Services/CustomName/classes/class.ilCustomName.php');
        include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
        include_once('./Services/CustomName/classes/class.ilCustomNameFormGUI.php');

        $this->setTabs();
        $ilTabs->activateTab("id_list");
        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "view"));


        $my_panel = ilPanelGUI::getInstance();

        $cname = new ilCustomName();
        $user_data = $cname->getDataFromCurrentUser();

        $usr_tpl = new ilTemplate("tpl.user_template.html", true, true, "Services/CustomName");

        $usr_tpl->setCurrentBlock("user_block");
        /**
         * placeholders for the template with user data(ID, CITY, COUNTRY, TITLE, TYPE)
         */
        foreach ($user_data as $key => $value){
            $usr_tpl->setVariable(strtoupper($key), $value);
        }
        // MYLINK is a placeholder to my template tpl.user_template.html
        $usr_tpl->setVariable("MY_LINK", $ilCtrl->getLinkTarget($this, "createForm"));
        $usr_tpl->setVariable("TABLE_LINK", $ilCtrl->getLinkTarget($this, "viewTableList"));

        //$usr_tpl->setVariable("LINK_HREF", $ilCtrl->getLinkTargetByClass("ilCustomNameFormGUI", "view"));
        $usr_tpl->parseCurrentBlock();

        $my_panel->setBody($usr_tpl->get());

        $tpl->setRightContent("Right Content");

        $tpl->setContent($my_panel->getHTML());

    }

    /**
     * Build property form
     */
    protected function createForm()
    {
        global $tpl, $ilTabs;

        $this->setTabs();
        $ilTabs->activateTab("id_form");

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init property form
     * @return object
     */
    protected function initForm()
    {
        global $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        $form_gui->setTitle('THE TITLE');
        $text_prop = new ilTextInputGUI("Put your Custom Name:", "name");
        $text_prop->setInfo("This is my advice");
        $form_gui->addItem($text_prop);

        $form_gui->addCommandButton('save','Save');
        $form_gui->addCommandButton('view','Cancel');

        return $form_gui;
    }

    /**
     * Save object data
     */
    protected function save()
    {
        global $ilCtrl;

        include_once('./Services/CustomName/classes/class.ilCustomName.php');

        $form_gui = $this->initForm();
        if ($form_gui->checkInput())
        {
            $obj = new ilCustomName();
            $obj->setId($form_gui->getInput("id"));
            $obj->setName($form_gui->getInput("name"));
            $obj->save();

            ilUtil::sendSuccess("Name created.", true);
            $ilCtrl->redirect($this, 'createForm');
        }
        else
        {

            ilUtil::sendFailure("Error creating the name.", true);
            $ilCtrl->redirect($this, 'createForm');
        }
    }

    /**
     * Delete object
     */
    protected function deleteRecords()
    {
        global $ilCtrl,$lng;

        require_once("Services/CustomName/classes/class.ilCustomName.php");
        $cname_ids = $this->getCustomNamesFromMultiAction();

        //if $cname_ids is not null, delete record
        if($cname_ids)
        {
            foreach($cname_ids as $cname_id)
            {
                $cname = new ilCustomName($cname_id);
                //$cname->setId((int)$cname_id);
                $cname->delete();
            }
        }

        ilUtil::sendSuccess("Custom Name deleted.");
        $ilCtrl->redirect($this,"viewTableList");
    }

    /**
     * Get custom names from data table multi action
     */
    protected function getCustomNamesFromMultiAction()
    {
        $cname_ids = $_REQUEST["row_id"];

        return $cname_ids;
    }

    /**
     * View data table
     */
    protected function viewTableList()
    {
        global $tpl,$ilTabs,$lng,$ilCtrl;

        $this->setTabs();
        $ilTabs->activateTab("id_list");

        include_once('./Services/CustomName/classes/class.ilCustomNameTableGUI.php');

        $table_gui = new ilCustomNameTableGUI($this, "viewTableList");

        //FILTERS
        $table_gui->setFilterCommand("applyFilter");
        $table_gui->setResetCommand("resetFilter");

        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * Apply filter
     */
    protected function applyFilter()
    {
        include_once('./Services/CustomName/classes/class.ilCustomNameTableGUI.php');
        $table_gui = new ilCustomNameTableGUI($this, "viewTableList");
        $table_gui->writeFilterToSession();        // writes filter to session
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $this->viewTableList();
    }

    /**
     * Reset filter
     */
    protected function resetFilter()
    {
        include_once('./Services/CustomName/classes/class.ilCustomNameTableGUI.php');
        $table_gui = new ilCustomNameTableGUI($this, "viewTableList");
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $table_gui->resetFilter();                // clears filter
        $this->viewTableList();
    }

    /**
     * TABS Setup
     */
    protected function setTabs()
    {
        global $ilTabs, $ilCtrl;

        $ilTabs->addTab("id_welcome", "WELCOME", $ilCtrl->getLinkTarget($this, "view"));
        $ilTabs->addTab("id_form", "INSERT NAMES", $ilCtrl->getLinkTarget($this, "createForm"));
        $ilTabs->addTab("id_list", "VIEW LIST", $ilCtrl->getLinkTarget($this, "viewTableList"));
        $ilTabs->addTab("id_tests", "Other tests", $ilCtrl->getLinkTarget($this,"viewUserDataWithPanel"));

        //$ilTabs->addTab("my_id", "Other Class", $ilCtrl->getLinkTargetByClass("ilCustomSecondClassGUI",""));

    }

}