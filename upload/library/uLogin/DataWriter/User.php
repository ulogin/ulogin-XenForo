<?php
class uLogin_DataWriter_User extends XenForo_DataWriter{
    
    protected function _getFields(){
        return array('xf_ulogin_users' => array(
            'userid'    => array('type' => self::TYPE_UINT),
            'identity' => array('type' => self::TYPE_STRING, 'required' => true),
            'network' => array('type' => self::TYPE_STRING, 'required' => true))
            );
    }
    
    protected function _getExistingData($data){
        if (!$id = $this->_getExistingPrimaryKey($data, 'userid')){
            return false;
        }
        return array('xf_ulogin_users' => $this->_getuLoginModel()->getUserById($id));
    }
    
    protected function _getUpdateCondition($tableName){
        return 'userid = ' . $this->_db->quote($this->getExisting('userid'));
    }
    
    protected function _getuLoginModel(){
        return $this->getModelFromCache ( 'uLogin_Model_User' );
    }
    
}
?>
