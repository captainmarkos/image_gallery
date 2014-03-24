<?php

// A database handle is required in the constructor.

class DBHelper {
    private $dbh;

    const INCORRECT_NUM_PARAMS =
        "Incorrect number of parameters supplied to construct_secure_query";

    const MISSING_DB_HANDLE = "DB handle is NULL";

    public function __construct($dbh=null) {
        if(!$dbh) {
            throw new InvalidArgumentException(self::MISSING_DB_HANDLE);
        }
        $this->dbh = $dbh;
    }

    private function _construct_secure_value_callback($value) {
        return "'" . mysqli_real_escape_string($this->dbh, $value) . "'";
    }

    private function _construct_secure_value($value) {
        $retval = is_array($value) ? $value : array($value);
        $retval = array_map(array($this, '_construct_secure_value_callback'), $retval);
        return join($retval, ', ');
    }

    public function construct_secure_query($sql, $params) {
        $retval = '';
        $secure_params = is_array($params) ? $params : array($params);
        $secure_params = array_map(array($this, '_construct_secure_value'), $secure_params);

        // Splitting the string first is much safer as we were running into
        // an issue with values that contain ?.
        $sql_parts = preg_split('/\?/', $sql);
        if(count($sql_parts) != count($secure_params) + 1) {
            throw new InvalidArgumentException(self::INCORRECT_NUM_PARAMS);
        }
        while($sql_part = array_shift($sql_parts)){
            $retval .= $sql_part . array_shift($secure_params);
        }
        return $retval;
    }
}

?>
