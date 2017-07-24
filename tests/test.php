<?php
/**
 * Created by PhpStorm.
 * User: sacdigital
 * Date: 06/07/17
 * Time: 20:49
 */


include_once __DIR__.'/../IdealProcess/IdealProcess.php';

$test = new IdealProcess();
$test->setController('teste','a2');
$test->run('test2',__DIR__.'/test_run.php acessController p1 p2 p3',[
    'auth'=>'yau76asdjas09aoas1'
]);



$test = new IdealProcess();
$test->setController('teste','a3');
$test->run('test2',__DIR__.'/test_run.php acessController p1 p2 p3',[
    'auth'=>'yau76asdjas09aoas1'
]);


$test = new IdealProcess();
$test->setController('teste','a4');
$test->run('test2',__DIR__.'/test_run.php acessController p1 p2 p3',[
    'auth'=>'yau76asdjas09aoas1'
]);