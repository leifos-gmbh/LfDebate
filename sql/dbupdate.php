<#1>
<?php
$fields = [
	"obj_id" => [
		"type" => "integer",
		"length" => 4,
		"notnull" => true
	],
	"is_online" => [
		"type" => "integer",
		"length" => 1,
		"notnull" => false
	]
];
if(!$ilDB->tableExists("rep_robj_xdbt_data")) {
	$ilDB->createTable("rep_robj_xdbt_data", $fields);
	$ilDB->addPrimaryKey("rep_robj_xdbt_data", ["obj_id"]);
}
?>
<#2>
<?php
$fields = [
    "xdbt_obj_id" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "child" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "parent" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ]
];
if(!$ilDB->tableExists("xdbt_post_tree")) {
    $ilDB->createTable("xdbt_post_tree", $fields);
    $ilDB->addPrimaryKey("xdbt_post_tree", ["xdbt_obj_id", "child"]);
}
?>
<#3>
<?php
?>
<#4>
<?php
$fields = [
    "id" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "user_id" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "title" => [
        "type" => "text",
        "length" => 200,
        "notnull" => false
    ],
    "description" => [
        "type" => "clob",
        "notnull" => false
    ],
    "type" => [
        "type" => "text",
        "length" => 20,
        "notnull" => false
    ],
    "create_date" => [
        "type" => "timestamp",
        "notnull" => false
    ],
    "last_update" => [
        "type" => "timestamp",
        "notnull" => false
    ],
    "version" => [
        "type" => "integer",
        "length" => 2,
        "notnull" => true,
        "default" => 0
    ]
];
if(!$ilDB->tableExists("xdbt_posting")) {
    $ilDB->createTable("xdbt_posting", $fields);
    $ilDB->addPrimaryKey("xdbt_posting", ["id", "version"]);
}
?>
<#5>
<?php
if ($ilDB->tableExists("rep_robj_xdbt_data") && !$ilDB->tableExists("xdbt_data")) {
    $ilDB->renameTable("rep_robj_xdbt_data", "xdbt_data");
}
?>
<#6>
<?php
if ($ilDB->tableColumnExists("xdbt_posting", "last_update")) {
    $ilDB->dropTableColumn("xdbt_posting", "last_update");
}
?>
<#7>
<?php
if($ilDB->tableExists("xdbt_posting") && !$ilDB->tableExists("xdbt_posting_seq")) {
    $ilDB->createSequence("xdbt_posting");
}
?>
<#8>
<?php
if (!$ilDB->tableColumnExists("xdbt_data", "default_sortation")) {
    $ilDB->addTableColumn("xdbt_data", "default_sortation", [
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 1
    ]);
}
?>
<#9>
<?php
$ilDB->manipulateF("DELETE FROM xdbt_posting WHERE " .
    " version > %s",
    ["integer"],
    [0]
);

?>
<#10>
<?php
$fields = [
    "id" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "posting_id" => [
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ],
    "rid" => [
        "type" => "text",
        "length" => 4000,
        "notnull" => true,
        "default" => 0
    ],
    "create_version" => [
        "type" => "integer",
        "length" => 2,
        "notnull" => false,
        "default" => 0
    ],
    "delete_version" => [
        "type" => "integer",
        "length" => 2,
        "notnull" => false,
        "default" => 0
    ]
];
if(!$ilDB->tableExists("xdbt_posting_att")) {
    $ilDB->createTable("xdbt_posting_att", $fields);
    $ilDB->addPrimaryKey("xdbt_posting_att", ["id"]);
    $ilDB->createSequence("xdbt_posting_att");
}
?>
