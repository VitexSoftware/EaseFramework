<?php
require_once '../tests/Bootstrap.php';

$sql     = \Ease\Shared::db();
$records = $sql->queryToArray('SELECT * FROM "public"."test"');

var_dump($records);

