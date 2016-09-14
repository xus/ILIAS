<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Custom Name GUI class
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 *
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
                $cmd = $ilCtrl->getCmd("viewuserdatawithpanel");
                //Ok I had to add "view" to this array to be able to pass to another method in the same class!
                if (in_array($cmd, array("viewuserdatawithpanel", "viewForm", "createForm", "save", "viewTableList")))
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
    function view()
    {
        global $tpl;

        $tpl->setContent("Hi!");
    }

    /**
     * Custom view with template
     *
     */
    function viewCustom()
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
    function viewUserData()
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
    function viewUserDataWithPanel()
    {
        global $tpl, $ilCtrl;

        include_once('./Services/CustomName/classes/class.ilCustomName.php');
        include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
        include_once('./Services/CustomName/classes/class.ilCustomNameFormGUI.php');

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
    public function createForm()
    {
        global $tpl;

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init property form
     * @return object
     */
    public function initForm()
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
    public function save()
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
            //Return to the previous view, I have to show success message to the user.
            $ilCtrl->redirect($this, 'createForm');
        }
        else
        {
            //Return to the previous view, I have to show an error message to the user.
            $ilCtrl->redirect($this, 'createForm');
        }
    }

    /**
     * View data table
     */
    public function viewTableList()
    {
        global $tpl;

        include_once('./Services/CustomName/classes/class.ilCustomNameTableGUI.php');

        $table_gui = new ilCustomNameTableGUI($this);

        $tpl->setContent($table_gui->getHTML());
    }
}