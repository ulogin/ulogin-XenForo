<?php
class uLogin_Installer{
    protected static $table = array('createQuery' => "CREATE TABLE IF NOT EXISTS ul_user(uid INTEGER, identity VARCHAR(256), 
                                                      PRIMARY KEY(uid), FOREIGN KEY (uid) REFERENCES xf_user(user_id) ON DELETE CASCADE)
                                                      ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;",
                                    'dropQuery' => "DROP TABLE IF EXISTS ul_user;");
    
    static function install($existingAddOn,$addOnData){
        $db = XenForo_Application::get('db');
        $db->query(self::$table['createQuery']);
        return true;
    }
    
    static function uninstall($data)
    {
        $db = XenForo_Application::get('db');
        $db->query(self::$table['dropQuery']);
        return true;
    }
}
?>
