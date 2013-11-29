<?php
class AmModel 
{
    // DB name 
    private static $dbname = 'amanager';
    // DB table prefix
    #private static $tblpfx = 'ama_';
    // DB file suffix 
    private static $dbfilesfx = '.sql';
    // File names 
    private static $tables = array(
            'settings' => 'settings',
            'templates' => 'templates'
    );
    private static $tpls = array();


    private static $properties = array(); 
    private $ampdo;

    public function __construct($input)
    { 
        // Create (connect to) SQLite database in file
        try
        {
            $this->ampdo = new PDO('sqlite:'.GSPLUGINPATH.AMPLUGIN.'/db/amanager.sdb');
        } catch(PDOException $e)
        {
            die($e->getMessage());
        }
        // Check setup DB
        if (file_exists(GSPLUGINPATH.AMPLUGIN.'/setup/setup.php')) 
        {
            // run setup process 
            include(GSPLUGINPATH.AMPLUGIN.'/setup/setup.php');
            if(!AmSetupDb::setup($this->ampdo, self::$tables['settings'].self::$dbfilesfx))
                die('Setup Process Error');
            // todo: delete setup directory
        }
        // grab templates 
        $sth = $this->ampdo->prepare('SELECT * FROM '.self::$tables['templates']);
        $sth->execute();
        /* Fetch all of the remaining rows in the result set */
        $tpls = $sth->fetchAll(PDO::FETCH_ASSOC);

        

            /*
             // Select all data from file db messages table 
            $result = $this->ampdo->query('SELECT * FROM '.self::$dbf['settings']);
 
            //$result->exec();
            var_dump($result);
            foreach ($result as $s) 
            {
                echo $s['key'];
            }*/
            /*
            foreach ($result as $m) {
                // Bind values directly to statement variables
                $stmt->bindValue(':id', $m['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':title', $m['title'], SQLITE3_TEXT);
                $stmt->bindValue(':message', $m['message'], SQLITE3_TEXT);
 
                // Format unix time to timestamp
                $formatted_time = date('Y-m-d H:i:s', $m['time']);
                $stmt->bindValue(':time', $formatted_time, SQLITE3_TEXT);
 
                // Execute statement
                $stmt->execute();
            }*/

/*
            $file_db = new PDO('sqlite:messaging.sqlite3');
            // Set errormode to exceptions
            $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
            // Create new database
            $memory_db = new PDO('sqlite::memory:');
            // Set errormode to exceptions
            $memory_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
 
    // Create table messages
    $file_db->exec("CREATE TABLE IF NOT EXISTS messages (
                    id INTEGER PRIMARY KEY, 
                    title TEXT, 
                    message TEXT, 
                    time INTEGER)");
 
    // Create table messages with different time format
    $memory_db->exec("CREATE TABLE messages (
                      id INTEGER PRIMARY KEY, 
                      title TEXT, 
                      message TEXT, 
                      time TEXT)");
 
  
    // Array with some test data to insert to database             
    $messages = array(
                  array('title' => 'Hello!',
                        'message' => 'Just testing...',
                        'time' => 1327301464),
                  array('title' => 'Hello again!',
                        'message' => 'More testing...',
                        'time' => 1339428612),
                  array('title' => 'Hi!',
                        'message' => 'SQLite3 is cool...',
                        'time' => 1327214268)
                );
 
 
    // Prepare INSERT statement to SQLite3 file db
    $insert = "INSERT INTO messages (title, message, time) 
                VALUES (:title, :message, :time)";
    $stmt = $file_db->prepare($insert);
 
    // Bind parameters to statement variables
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':time', $time);
 
    // Loop thru all messages and execute prepared insert statement
    foreach ($messages as $m) {
      // Set values to bound variables
      $title = $m['title'];
      $message = $m['message'];
      $time = $m['time'];
 
      // Execute statement
      $stmt->execute();
    }
 
    // Prepare INSERT statement to SQLite3 memory db
    $insert = "INSERT INTO messages (id, title, message, time) 
                VALUES (:id, :title, :message, :time)";
    $stmt = $memory_db->prepare($insert);
 
    // Select all data from file db messages table 
    $result = $file_db->query('SELECT * FROM messages');
 
    // Loop thru all data from messages table 
    // and insert it to file db
    foreach ($result as $m) {
      // Bind values directly to statement variables
      $stmt->bindValue(':id', $m['id'], SQLITE3_INTEGER);
      $stmt->bindValue(':title', $m['title'], SQLITE3_TEXT);
      $stmt->bindValue(':message', $m['message'], SQLITE3_TEXT);
 
      // Format unix time to timestamp
      $formatted_time = date('Y-m-d H:i:s', $m['time']);
      $stmt->bindValue(':time', $formatted_time, SQLITE3_TEXT);
 
      // Execute statement
      $stmt->execute();
    }
 
    // Quote new title
    $new_title = $memory_db->quote("Hi''\'''\\\"\"!'\"");
    // Update old title to new title
    $update = "UPDATE messages SET title = {$new_title} 
                WHERE datetime(time) > 
                datetime('2012-06-01 15:48:07')";
    // Execute update
    $memory_db->exec($update);
 
    // Select all data from memory db messages table 
    $result = $memory_db->query('SELECT * FROM messages');
 
    foreach($result as $row) {
      echo "Id: " . $row['id'] . "\n";
      echo "Title: " . $row['title'] . "\n";
      echo "Message: " . $row['message'] . "\n";
      echo "Time: " . $row['time'] . "\n";
      echo "\n";
    }
 
 
    // Drop table messages from file db
    $file_db->exec("DROP TABLE messages");
    // Drop table messages from memory db
    $memory_db->exec("DROP TABLE messages");
 
 
    // Close file db connection
    $file_db = null;
    // Close memory db connection
    $memory_db = null;
  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }*/
    }

    /* $context */
    public function getTplContext($query) 
    {
    
    }

    /* $context, $section */
    public function getTplSection($query) 
    {
    
    }

    /* $context, $section, $title */
    public function getPropertyColumn($query) 
    {   //secured query with prepare and execute
        $args = func_get_args();
        //first element is not an argument but the query itself, should removed
        array_shift($args);
        $reponse = $this->ampdo->prepare($query);
        $reponse->execute($args);
        return $reponse->fetchColumn(1); 
    }

    public function getPropertyArray($query)
    {   //secured query with prepare and execute
        $args = func_get_args();
        //first element is not an argument but the query itself, should removed
        array_shift($args);
        $reponse = $this->ampdo->prepare($query);
        $reponse->execute($args);
        return $reponse->fetchAll(PDO::FETCH_ASSOC);
    }


    public function toArray($input)
    {
        return preg_split('/[\s,;]+/', $input );
    }

}
?>
