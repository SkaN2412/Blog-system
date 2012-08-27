<?php
inviException::__init(0);

try {
DB::connect();
} catch ( PDOException $e ) {
    print( $e->getMessage() );
}
?>