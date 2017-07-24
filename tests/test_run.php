<?php
/**
 * Created by PhpStorm.
 * User: sacdigital
 * Date: 06/07/17
 * Time: 20:53
 */

print_r($argv);

$_argv = $argv[2];

$_argv = str_replace('data=','',$_argv);
$_argv = json_decode($_argv);

while(true){



    print_r($_argv);
    echo $argv;

    echo PHP_EOL," Print test ".time(),PHP_EOL;

    sleep(rand(1,3));
}