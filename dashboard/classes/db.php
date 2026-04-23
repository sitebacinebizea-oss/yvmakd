<?php
  /**
   * PDO — تهيئة من config.php (مثل مشروع الصديق) مع fallback إن لم تُعرّف الثوابت.
   */
  class DB
  {
    private $host;
    private $user;
    private $pass;
    private $name;
    private $charset;

    private $dbh;
    private $error;
    private $stmt;

    private $options = array(
      PDO::ATTR_PERSISTENT => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_TIMEOUT => 5
    );

    public function __construct()
    {
      if (!defined('DB_HOST')) {
        $mysql_url = getenv('MYSQL_URL');
        if ($mysql_url) {
          $url_parts = parse_url($mysql_url);
          if (is_array($url_parts) && !empty($url_parts['host'])) {
            define('DB_HOST', (string) $url_parts['host']);
            define('DB_PORT', isset($url_parts['port']) ? (string) (int) $url_parts['port'] : '3306');
            define('DB_USER', (string) ($url_parts['user'] ?? 'root'));
            $rp = $url_parts['pass'] ?? '';
            define('DB_PASSWORD', $rp !== '' ? rawurldecode((string) $rp) : '');
            define('DB_NAME', isset($url_parts['path']) ? ltrim((string) $url_parts['path'], '/') : 'railway');
            define('DB_CHARSET', 'utf8mb4');
          }
        }
        if (!defined('DB_HOST')) {
          define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
          define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
          define('DB_USER', getenv('MYSQLUSER') ?: 'root');
          define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');
          define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway');
          define('DB_CHARSET', 'utf8mb4');
        }
      }

      $this->host = DB_HOST;
      $this->user = DB_USER;
      $this->pass = DB_PASSWORD;
      $this->name = DB_NAME;
      $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

      $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
      $dsn = 'mysql:host=' . $this->host . ';port=' . $port . ';dbname=' . $this->name . ';charset=' . $this->charset;

      try {
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $this->options);
      } catch (PDOException $e) {
        $this->error = $e->getMessage();
        error_log("Database Connection Error: " . $e->getMessage());
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "<br>Host: " . $this->host . "<br>Database: " . $this->name);
      }
    }
    
    public function query($query)
    {
      // التحقق من وجود الاتصال قبل تنفيذ الاستعلام
      if ($this->dbh === null) {
        die("خطأ: الاتصال بقاعدة البيانات غير موجود. الرجاء التحقق من بيانات الاتصال.");
      }
      
      try {
        $this->stmt = $this->dbh->prepare($query);
      } catch (PDOException $e) {
        die("خطأ في تحضير الاستعلام: " . $e->getMessage());
      }
    }
    
    // $param : placeholder value that we will be using in our SQL statement
    // $value : the actual value that we want to bind to the placeholder
    // $type  : the datatype of the parameter
    public function bind($param, $value, $type = null)
    {
      if(is_null($type)) {
        switch(true) {
          case is_int($value):
            $type = PDO::PARAM_INT;
          break;
          case is_bool($value):
            $type = PDO::PARAM_BOOL;
          break;
          case is_null($value):
            $type = PDO::PARAM_NULL;
          break;
          default:
            $type = PDO::PARAM_STR;
          break;
        }
      }
      $this->stmt->bindValue($param, $value, $type);
    }
    
    public function execute()
    {
      try {
        return $this->stmt->execute();
      } catch (PDOException $e) {
        die("خطأ في تنفيذ الاستعلام: " . $e->getMessage());
      }
    }
    
    public function fetch()
    {
      $this->execute();
      return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function fetchAll()
    {
      $this->execute();
      return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    // returns the number of effected rows from the previous delete, update or insert statement
    public function rowCount()
    {
      return $this->stmt->rowCount();
    }
    
    // returns the last inserted Id as a string
    public function lastInsertId()
    {
      return $this->dbh->lastInsertId();
    }
    
    public function beginTransaction()
    {
      return $this->dbh->beginTransaction();
    }
    
    public function endTransaction()
    {
      return $this->dbh->commit();
    }
    
    public function cancelTransaction()
    {
      return $this->dbh->rollBack();
    }
    
    //dumps the the information that was contained in the Prepared Statement
    public function debugDumpParams()
    {
      return $this->stmt->debugDumpParams();
    }
  }
?>
