<?php

function autoload($name){
	include_once __DIR__.'/'.$name.'.php';
}

spl_autoload_register('autoload');