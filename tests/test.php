<?php
/**
 * Created by PhpStorm.
 * User: sacdigital
 * Date: 06/07/17
 * Time: 20:49
 */


include_once __DIR__.'/../IdealProcess/IdealProcess.php';

$test = new IdealProcces();
$test->run('test',__DIR__.'/test_run.php');