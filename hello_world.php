<?php
include ("./include/inc.header.php");
include_once("./Services/CustomName/classes/class.ilCustomNameGUI.php");

// this way ilCtrl will hand over the flow of control to ilCustomNameGUI
// if you get a "Could not find entry in modules.xml or services.xml for ilcustomnamegui"
// error, you must reload the ctrl structure information in the setup first (it reads the
// service.xml) setup -> client -> details -> tools -> reload
header("Location: ilias.php?baseClass=ilCustomNameGUI");
exit;


/**
 * Example 1: Service execution
 *
 * alex: this way ilCtrl would not be informed that the ilCustomNameGUI has been called
 */
$cname = new ilCustomNameGUI();
$cname->executeCommand();


/**
 * Example 2: Hello world using one template from a service.
 */
/*
global $tpl;

$tpl->getStandardTemplate();

$ilLocator->addRepositoryItems();
$ilLocator->addItem('breadcrumb item 1', 'http://leifos.com');
$ilLocator->addItem('breadcrumb item 2', 'http://google.com');
$tpl->setLocator();

$tpl->setTitle("testing title");

$tpl->setRightContent("Right Content");

//main content
$my_tpl = new ilTemplate("tpl.my_template.html", true, true, "Services/CustomName");

$my_tpl->setCurrentBlock("my_block");
$my_tpl->setVariable("TEXT", "This is the content for the TEXT placeholder");
$my_tpl->setVariable("VALUE", "This is the content for the VALUE placeholder");
$my_tpl->parseCurrentBlock();

//$tpl->setVariable("LINK_HREF", $ilCtrl->getLinkTargetByClass("iltestgui", "view"));

$tpl->setContent($my_tpl->get());

$tpl->show();
*/