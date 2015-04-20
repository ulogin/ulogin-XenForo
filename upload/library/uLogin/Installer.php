<?php

class uLogin_Installer {
	protected static $table = array('createQuery' => 'CREATE TABLE IF NOT EXISTS xf_ulogin_users(
id INTEGER NOT NULL auto_increment,
userid INTEGER NOT NULL,
identity VARCHAR(256) NOT NULL,
network VARCHAR(256) NOT NULL,
 PRIMARY KEY(id))
 ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

	static function install($existingAddOn, $addOnData)
	{
		$db = XenForo_Application::getDb();
		$db->query(self::$table['createQuery']);
		return true;
	}

	static function uninstall($data)
	{
		$db = XenForo_Application::getDb();
		return true;
	}
}

?>
