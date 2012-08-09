<?php
/*
 * A little module for executing MySQL query safely, if there's will be only one data array.
 */
class inviPDO extends PDO {
    public $stmt;
    
    /*
     * Method for prepare and execute query
     * It requires query to execute
     * Parameter $data is not nessesary, it's required only in case of holders in query given
     */
    public function query( $query, $data = array() )
    {
        try {
            $this->stmt = $this->prepare($query);
            $this->stmt->execute( (array)$data );
        } catch ( PDOException $e ) {
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }
        return TRUE;
    }
    
    /*
     * Method for getting returned data if any
     */
    public function fetch( $mode = "assoc")
    {
        try {
            // Set fetch mode
            switch ($mode)
            {
                case "assoc":
                    $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
                    break;
                case "num":
                    $this->stmt->setFetchMode(PDO::FETCH_NUM);
                    break;
                default:
                    throw new inviException(1, "Unknown fetch mode");
            }
            
            // If there's nothing returned, return NULL
            if ( $this->stmt->rowCount() < 1 )
            {
                return NULL;
            }
            
            // Fetch all entries into multi-dimensional array
            $data = array();
            while ( $row = $this->stmt->fetch() )
            {
                $data[] = $row;
            }
            
            // Return full array
            return $data;
        } catch ( PDOException $e ) {
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }
    }
}
?>