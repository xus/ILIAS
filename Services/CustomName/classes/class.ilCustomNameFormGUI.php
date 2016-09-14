<?php
/**
 * CustomNameForm GUI class
 *
 * @version $Id$
 */
class CustomNameFormGUI
{
    function __construct()
    {
        global $tpl;

        $tpl->getStandardTemplate();
    }
    function executeCommand()
    {
        global $ilCtrl, $tpl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class)
        {
            default:
                $cmd = $ilCtrl->getCmd("view");
                if(in_array($cmd, array("view")))
                {
                    $this->$cmd();
                }
                break;
        }
        $tpl->show();
    }
    public function view()
    {
        global $tpl, $ilCtrl, $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle('Title test form');

        $text_input = new ilTextInputGUI('InputText');

        $form->addItem($text_input);

        $form->addCommandButton('test','test2');
        $form->addCommandButton('view',$lng->txt('save'));


        $tpl->setContent($form->getHTML());

    }
}