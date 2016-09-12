<?php

require_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');

/*
 * CustomForm repository object plugin.
 *
 * @author  Jesús López <lopez@leifos.com>
 *
 * @version $Id$
 */

//TODO: start here.
//ilRepositoryObjectPlugin has this 2 abstract methods.

class ilCustomFormPlugin extends ilRepositoryObjectPlugin{

    protected static $instance;

    /**
     * @return ilCustomFormPlugin
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * @return	string	Plugin Name
     */
    final function getPluginName()
    {
        return "Custom Form";
    }

    function uninstallCustom()
    {
        // TODO: Implement uninstallCustom() method.
    }
    function getPluginName()
    {
        // TODO: Implement getPluginName() method.
    }
}