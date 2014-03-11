<?php
class __moduleName__ extends mmProg {
    
    public function executeIndex(mmRequest $request) {
        $this->listeRecord = Doctrine_Core::getTable('__tableName__')->findAll();
    }
    
    public function executeEdit(mmRequest $request) {
        
    }
    
    public function executeDelete(mmRequest $request) {
        
    }
    
}
