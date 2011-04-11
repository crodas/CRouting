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
var_dump($routing->match('/1-zaction.html'));
var_dump($routing->match('/1-zaction.js'));
var_dump($routing->match('/foo-zaction.js'));
