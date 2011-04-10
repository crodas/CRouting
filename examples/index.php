<?php

require "../lib/CRouting.php";

/**
 *  Create the CRouting object using the routes files
 *  and a temporary folder when its compiled version 
 *  is stored
 */
$routing = new CRouting('./simple.yml', './generated');

/* test, false will be return if it failed */
var_dump($routing->match('/'));
var_dump($routing->match('/zcontroller'));
var_dump($routing->match('/zcontroller/zaction'));
