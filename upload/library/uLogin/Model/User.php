<?php
class uLogin_Model_User extends XenForo_Model{
    public function getUserIdByIdentity($identity){
          return $this->_getDb()->fetchRow('SELECT uid FROM ul_user WHERE identity = ?', $identity);
    }
    
    public function getIdentityByUserId($uid){
          return $this->_getDb()->fetchRow('SELECT identity FROM ul_user WHERE uid = ?', $uid);
    }
    
    public function getUserById($uid){
          return $this->_getDb()->fetchRow('SELECT * FROM ul_user WHERE uid = ?', $uid);
    }
    
    public function getAllUsers(){
         return $this->fetchAllKeyed('SELECT * FROM ul_user ORDER BY uid DESC', 'uid');
    }
}
?>
