<?php

/**
 *
 * @author sergio
 */
abstract class DatabaseTable {
    
    /**
     * @var boolean
     */
    protected $saved;
    
    /**
     * @var boolean 
     */
    protected $isNew;
    
    public function isSaved() {
        return $this->saved;
    }
    
    public function isNew() {
        return $this->isNew;
    }
    
    public abstract function save();
    public abstract function delete();
    
    protected static abstract function newFromDatabaseRow( $row );
}
