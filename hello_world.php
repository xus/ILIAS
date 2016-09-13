<?php
include ("./include/inc.header.php");
include_once("./Services/CustomName/classes/class.ilCustomNameGUI.php");

/**
 * Example 1: Service execution
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