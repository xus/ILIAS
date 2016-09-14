<#1>
<?php
if (!$ilDB->tableExists('srv_cname_data')) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true
        ),
    );

    $ilDB->createTable("srv_cname_data", $fields);
    $ilDB->addPrimaryKey("srv_cname_data", array("id"));
}
?>
<#2>
<?php
//Take care with this, I've commented this sequence because I created this in other way.
//$ilDB->createSequence("srv_cname_data");
?>