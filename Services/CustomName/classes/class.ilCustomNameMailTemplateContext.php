<?php
include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

class ilCustomNameMailTemplateContext extends ilMailTemplateContext
{
    const ID = 'crs_context_member_manual';

    function getId()
    {
        return self::ID;
    }
    function getTitle()
    {
        return 'crs_context_member_manual';
    }
    function getSpecificPlaceholders()
    {
        $placeholders['crs_title'] = array(
            'placeholder'	=> 'NAME',
            'label'			=> 'Custom Name'
        );

        return $placeholders;
    }
    function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
    {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $ilObjDataCache;

        if('crs_title' == $placeholder_id)
        {
            return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
        }
        else if('crs_link' == $placeholder_id)
        {
            require_once './Services/Link/classes/class.ilLink.php';
            return ilLink::_getLink($context_parameters['ref_id'], 'crs');
        }

        return '';
    }

    function getDescription()
    {
        return 'This is the description';
    }
}