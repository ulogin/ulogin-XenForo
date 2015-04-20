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

//		$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
//		$uLoginGroup = $uLoginModel->getGroupIdByTitle('uLoginGroup');
//		if (!$uLoginGroup)
//		{
//			$uLoginModel = XenForo_Model_User::create('XenForo_Model_UserGroup');
//			$GroupInfo = $uLoginModel->getUserGroupById(2);
//
//			$uLoginModel = XenForo_Model_User::create('XenForo_Model_Permission');
//			$GroupPermissions = $uLoginModel->getAllPermissionsWithValues(2);
//
//			$uLoginModel = XenForo_Model_User::create('XenForo_Model_UserGroup');
//			$userGroupInfo = array('title' => 'uLoginGroup', 'display_style_priority' => $GroupInfo['display_style_priority'], 'username_css' => $GroupInfo['username_css'], 'user_title' => '', 'banner_text' => $GroupInfo['banner_text'], 'banner_css_class' => $GroupInfo['banner_css_class'],);
//			$uLoginGroupId = $uLoginModel->updateUserGroupAndPermissions(0, $userGroupInfo, $GroupPermissions);
//		}

		return true;
	}

	static function uninstall($data)
	{
		$db = XenForo_Application::getDb();
		return true;
	}
}

?>
