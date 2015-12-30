<?php
namespace OC;

class DatabaseSessionHandler implements \SessionHandlerInterface {
    protected static $dsns = array(
        "mysql" => "mysql:host=%s;dbname=%s"
    );
    
    const READ_SESSION_QUERY = "SELECT `data` FROM `sessions` WHERE `id` = :id";
    const WRITE_SESSION_QUERY = "REPLACE INTO `sessions` VALUES (:id, :data, :ts)";
    const DELETE_SESSION_QUERY = "DELETE FROM `sessions` WHERE `id` = :id";
    const DELETE_EXPIRED_SESSION_QUERY = "DELETE FROM `sessions` WHERE `ts` < :expired";
    
    /**
     * the database type
     */
    private $dbtype;
    
    /**
     * the database host
     */
    private $host;
    
    /**
     * the database user name
     */
    private $user;
    
    /**
     * the database user password
     */
    private $password;
    
    /**
     * the database name
     */
    private $database;
    
    /**
     * the data object
     */
    private $conn;
    
    public function __construct () {
        
    }
    
    /**
     * store the database source info
     * @param string database type
     * @param string database host
     * @param string database user
     * @param string database user password
     * @param string database name
     * @return void
     */
    public function init ($dbtype, $host, $user, $password, $database) {
        $this->dbtype = $dbtype;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
    }
    
    /**
     * Open the database connection
     * @return bool
     */
    public function open ($save_path , $session_id) {
        $dsn = sprintf(self::$dsns[$this->dbtype], $this->host, $this->database);
        
        $this->conn = new \PDO($dsn, $this->user, $this->password);
        
        return true;
    }
    
    /**
     * Read the session
     * @param string session id
     * @return string sessoin id
     */
    public function read ($session_id) {
        $stmt = $this->conn->prepare(self::READ_SESSION_QUERY);
        
        $stmt->bindParam(":id", $session_id);
        
        $result = $stmt->execute();
        $count = $stmt->rowCount();
        
        if ($result && $count) {
            $record = $stmt->fetch(\PDO::FETCH_OBJ);
            
            return $record ? $record->data : "";
        }
        
        return "";
    }
    
    /**
     * Write the session
     * @param string session id
     * @param string session data
     */
    public function write ($session_id , $session_data) {
        $ts = time();
        
        $stmt = $this->conn->prepare(self::WRITE_SESSION_QUERY);
        
        $stmt->bindParam(":id", $session_id);
        $stmt->bindParam(":data", $session_data);
        $stmt->bindParam(":ts", $ts);
        
        return $stmt->execute();
    }
    
    /**
     * Close the database connection
     * @return bool
     */
    public function close () {
        $this->conn = null;
        
        return true;
    }
    
    /**
     * Destoroy the session
     * @param string session id
     * @return bool
     */
    public function destroy ($session_id) {
        $stmt = $this->conn->prepare(self::DELETE_SESSION_QUERY);
        
        $stmt->bindParam(":id", $session_id);
        
        return $stmt->execute();
    }
    
    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     */
    public function gc ($maxlifetime) {
        $expired = time() - $maxlifetime;
        
        $stmt = $this->conn->prepare(self::DELETE_EXPIRED_SESSION_QUERY);
        
        $stmt->bindParam(":expired", $expired);
        
        return $stmt->execute();
    }
}
