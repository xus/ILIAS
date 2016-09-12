<#1>
<?php
    //I have to put the correct name for this table.
    if(!$ilDB->tableExists('custom_form')) {
        $fields = array(
            'obj_id' => array (
                'type' => 'integer',
                'length' => 5,
                'notnull' => true
            ),
            'name' => array (
                'type' => 'text',
                'length' => 400,
                'notnull' => true
            )
        );

        $ilDB->createTable('custom_form');
        $ilDB->addPrimaryKey('custom_form','id');
    }
?>