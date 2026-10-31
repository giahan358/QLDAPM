<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require_once dirname(__DIR__,2).'/user/model/connectDB.php';
// Read-only validation against localhost: print counts only, never customer/payment data.
$db=connectdb();
$source=file_get_contents(dirname(__DIR__,2).'/admin/admin/controller/after-sales.php');
preg_match_all('/\$db->query\("(SELECT [^"\r\n]+)"\)/',$source,$queries);
if(count($queries[1])!==2)throw new RuntimeException('Expected payment and review queries');
foreach($queries[1] as $sql){$rows=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);echo 'PASS admin overview query; rows='.count($rows).PHP_EOL;}
