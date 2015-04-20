<?php
class uLogin_Model_User extends XenForo_Model{
    public function getUserIdByIdentity($identity){
          return $this->_getDb()->fetchRow('SELECT userid FROM xf_ulogin_users WHERE identity = ?', $identity);
    }
    
    public function getIdentityByUserId($userid){
          return $this->_getDb()->fetchRow('SELECT identity FROM xf_ulogin_users WHERE userid = ?', $userid);
    }
    
    public function getUserById($userid){
          return $this->_getDb()->fetchRow('SELECT * FROM xf_ulogin_users WHERE userid = ?', $userid);
    }

    public function getUserByEmail($useremail){
          return $this->_getDb()->fetchRow('SELECT user_id FROM xf_user WHERE email = ?', $useremail);
    }

    public function getAllUsers(){
         return $this->fetchAllKeyed('SELECT * FROM xf_ulogin_users ORDER BY userid DESC', 'userid');
    }

    public function getXenForoUser($userid){
         return  $this->_getDb()->fetchRow('SELECT user_id FROM xf_user WHERE user_id = ?', $userid);
    }

    public function deleteUloginUser($identity){
         return  $this->_getDb()->fetchRow('DELETE FROM xf_ulogin_users WHERE identity = ?', urldecode($identity));
    }

    public function networksUloginUser($user_id){
         return  $this->_getDb()->fetchRow('SELECT network FROM xf_ulogin_users WHERE userid = ?', $user_id);
    }

    public function getAllUsersById($user_id){
        return $this->fetchAllKeyed('SELECT * FROM xf_ulogin_users  WHERE  userid= '.$user_id,'');
    }
}
?>
