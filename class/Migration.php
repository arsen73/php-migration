<?php

class Migration {
	
	private $config;

	private $cdb;
	private $sql;

	public function __construct(){
		$this->config = include __DIR__ . '/config.php';
		$this->connection();
	}

	/**
	* Возвращает переменную из конфигурации
	*/
	public function getConfig($name=null){
		if(!is_null($name)){
			if(isset($this->config[$name])){
				$a = $this->config;
				return $a[$name];
			}
			else{
				return false;
			}
		}
		else{
			return $this->config;
		}
	}

	/**
	* Подключение к базе данных
	*/
	public function connection(){
		try{	
			$this->cdb = new PDO("mysql:host=".$this->getConfig("host").";dbname=".$this->getConfig('dbname'), $this->getConfig('user'), $this->getConfig('password'));
			$this->cdb->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$this->cdb->exec("USE ".$this->getConfig('dbname').";");
		}
		catch(PDOException $e){
			$this->errorLog($e->getMessage(), __LINE__, __FILE__, __FUNCTION__);
		}
	}

	/**
	* Запись ошибок
	*/
	public function errorLog($error, $line="", $file="", $function=""){
		echo "\033[31m"; //цвет вывода в консоль
		echo "\n\r DB Error at Line ".$line." in function ".$function."\n\r";
		echo "\033[0m";
		echo $error;
		echo "\n\r";
		echo "\033[0m";
	}

	protected function sql_exec(){
		if(strlen($this->sql)<2){
			$this->errorLog("Пустой запрос", __LINE__, __FILE__, __FUNCTION__);
			return false;
		}
		try{
			$this->cdb->exec($this->sql);
		}
		catch(PDOException $e){
			$this->errorLog($e->getMessage(), __LINE__, __FILE__, __FUNCTION__);
		}
	}

	/**
	* Cоздаёт файл с миграцией
	*/
	public function createMigration($name=""){
		if($name == ''){
			$name = 'm_'.date("Y_m_d_h_i_s", time(1));
		}
		$file = fopen($this->getConfig('migration_path').$name.'.php', 'w');
		if(!$file){
			die('Error create file');
		}
		$str = '<?php
		class '.$name.' extends Migration{
			public function up(){
				$this->addSql("");
			}

			public function down(){
				$this->addSql("");
			}
		}';
		if(!fwrite($file, $str)){
			die('Error write file');
		}
	}

	/**
	* Добавление sql запроса
	*/
	public function addSql($sql){
		$this->sql = $sql;
	}

	/**
	* Hаходит все файлы с миграциями
	*/
	protected function searchMigration(){
		$list_file = scandir($this->getConfig('migration_path'));
		foreach ($list_file as $k=>$file) {
			if($file == '.' || $file == '..'){
				unset($list_file[$k]);
			}
		}
		return $list_file;
	}

	/**
	* Выполнить все миграции или Откатить
	*/
	protected function AllMigrationExec($type = 'up'){
		$list = $this->searchMigration();
		foreach ($list as $migration) {
			$this->execMigration($migration, $type);
		}
	}

	/**
	* Выполнить все миграции
	*/
	public function UpAllMigration(){
		$this->AllMigrationExec('up');
	}

	/**
	* Откатить все миграции
	*/
	public function DownAllMigration(){
		$this->AllMigrationExec('down');
	}

	/**
	* Выполнить одну миграцию
	*/
	public function UpOneMigration($name){
		$this->execMigration($name, 'up');
	}

	/**
	* Откатить одну миграцию
	*/
	public function DownOneMigration($name){
		$this->execMigration($name, 'down');
	}

	/**
	* Выполнение миграции
	*/
	protected function execMigration($fileName, $type='up'){
		require $this->getConfig('migration_path').$fileName;
		$className = str_replace('.php', '', $fileName);
		$m = new $className;
		switch ($type) {
			case 'up':
				$m->up();
				$m->sql_exec();
				break;
			
			case 'down':
				$m->down();
				$m->sql_exec();
				break;
		}
	}

	public function printListMigration(){
		$list = $this->searchMigration();
		foreach ($list as $migration) {
			echo $migration."\n\r";
		}
	}

	public function up(){}

	public function down(){}
}