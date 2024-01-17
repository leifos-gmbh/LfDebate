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
if(!$ilDB->tableExists("xdbt_post_tree")) {
    $ilDB->createTable("xdbt_post_tree", $fields);
    $ilDB->addPrimaryKey("xdbt_post_tree", ["id", "version"]);
}
?>
