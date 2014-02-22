#!/usr/bin/php
<?php
include_once __DIR__ . '/class/autoload.php';
if(count($argv)==1){
	echo "
	run \t\t\t выполнит все миграции \n\r
	run name_migration \t выполнит одну миграцию \n\r
	down \t\t\t откатит все миграции \n\r
	down name_migration \t откатит одну миграцию \n\r
	list \t\t\t покажет сипоск миграций \n\r\n\r";
}

$m = new Migration();
switch ($argv[1]) {
	case 'run':
		if(isset($argv[2])){
			$m->UpOneMigration($argv[2]);
		}
		else
			$m->UpAllMigration();
		break;

	case 'down':
		if(isset($argv[2])){
			DownOneMigration($argv[2]);
		}
		else
			$m->DownAllMigration();
		break;

	case 'list':
		$m->printListMigration();
		break;
	
	default:
		echo "Не правильный параметр\n\r";
		break;
}
