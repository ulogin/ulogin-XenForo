<?php
class uLogin_Installer{
    protected static $table = array('createQuery' => "CREATE TABLE IF NOT EXISTS ul_user(uid INTEGER, identity VARCHAR(256), 
                                                      PRIMARY KEY(uid))
                                                      ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;",
                                    'dropQuery' => "DROP TABLE IF EXISTS ul_user;",
                                    'deleteQuery' => "DELETE a1 FROM xf_user AS a1 INNER JOIN ul_user AS a2
                                                      WHERE a1.user_id=a2.uid;");
    
    static function install($existingAddOn,$addOnData){
        $db = XenForo_Application::get('db');
        $db->query(self::$table['createQuery']);
        return true;
    }
    
    static function uninstall($data)
    {
        $db = XenForo_Application::get('db');
        $db->query(self::$table['deleteQuery']);
        $db->query(self::$table['dropQuery']);
        return true;
    }
}
?>
