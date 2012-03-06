<?php
class uLogin_DataWriter_User extends XenForo_DataWriter{
    
    protected function _getFields(){
        return array('ul_user' => array(
            'uid'    => array('type' => self::TYPE_UINT),
            'identity' => array('type' => self::TYPE_STRING, 'required' => true))
            );
    }
    
    protected function _getExistingData($data){
        if (!$id = $this->_getExistingPrimaryKey($data, 'uid')){
            return false;
        }
        return array('ul_user' => $this->_getuLoginModel()->getUserById($id));
    }
    
    protected function _getUpdateCondition($tableName){
        return 'uid = ' . $this->_db->quote($this->getExisting('uid'));
    }
    
    protected function _getuLoginModel(){
        return $this->getModelFromCache ( 'uLogin_Model_User' );
    }
    
}
?>
