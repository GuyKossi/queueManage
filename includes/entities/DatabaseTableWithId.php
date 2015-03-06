<?php

/**
 * Description of DatabaseTableWithId
 *
 * @author sergio
 */
abstract class DatabaseTableWithId extends DatabaseTable {
    
    /**
     * @var array
     */
    protected $columns;
    
    /**
     * @var string
     */
    protected $idColumn;
    
    /**
     * @var string
     */
    protected $tableName;

    protected static function fromDatabaseByParameterGeneric( $parName, $parValue, $class ) {
        $instance = new $class();
        $table = $instance->tableName;
        unset( $instance );

        $sql = "SELECT * FROM $table WHERE $parName=? LIMIT 1";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( array( $parValue ) ) ) {
            throw new Exception( __METHOD__ . ": Error while reading from database" );
        }
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        if ( $row ) {
            return self::newFromDatabaseRowGeneric( $row, $class );
        }
        return null;
    }
    
    protected static function newFromDatabaseRowGeneric( $row, $class ) {
        $ret = new $class();

        $idColumn = $ret->idColumn;
        $ret->$idColumn = $row[$idColumn];

        foreach ( $ret->columns as $column ) {
            $ret->$column = $row[$column];
        }

        $ret->saved = true;
        $ret->isNew = false;
        return $ret;
    }
    
    public function save() {
        if ( $this->saved ) {
            return true;
        }
        
        $idColumn = $this->idColumn;
        $parameters = array();
        foreach ( $this->columns as $column ) {
            $parameters[] = $this->$column;
        }
        
        if ( $this->isNew ) {
            $sql = "INSERT INTO $this->tableName (" . implode( ",", $this->columns ) . ") "
                    . "VALUES (". str_repeat("?,", count( $this->columns ) - 1 ) . "?)";
        } else {
            $sql = "UPDATE $this->tableName SET ";
            
            $sqlColumns = "";
            foreach ( $this->columns as $column ) {
                if ( empty($sqlColumns) ) {
                    // First iteration
                    $sqlColumns = "$column=?";
                } else {
                    $sqlColumns .= ",$column=?";
                }
            }
            $sql .= $sqlColumns;
            
            $sql .= " WHERE $idColumn=?";
            $parameters[] = $this->$idColumn;
        }
        
        $conn = Database::getConnection();
        $stmt = $conn->prepare( $sql );
        $result = $stmt->execute( $parameters );
        
        if ( $this->isNew ) {
            // Database has assigned an ID for the new desk
            $this->$idColumn = $conn->lastInsertId();
        }
        
        if ( !$result ) {
            return false;
        }
        $this->saved = true;
        $this->isNew = false;
        return true;
    }
    
    public function delete() {
        if ( $this->isNew ) {
            throw new Exception('Cannot delete new record');
        }
        
        $idColumn = $this->idColumn;
        $sql = "DELETE FROM $this->tableName WHERE $idColumn=?";
        $stmt = Database::prepareStatement( $sql );
        $result = $stmt->execute( array( $this->$idColumn ) );
        if ( !$result ) {
            //Deletion failed
            return false;
        }
        return true;
    }
}
