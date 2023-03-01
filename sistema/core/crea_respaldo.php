<?php
/**
 * This file contains the Backup_Database class wich performs
 * a partial or complete backup of any given MySQL database
 *
 *
 */

include("db.php");

// Report all errors
//error_reporting(0);

/**
 * Define database parameters here
 */
define("DB_USER", 'root');
define("DB_PASSWORD", '');
define("DB_NAME", 'sistema');
define("DB_HOST", 'localhost');
define("OUTPUT_DIR", 'backup_db');
define("TABLES", '*');

/**
 * Instantiate Backup_Database and perform backup
 */
$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$status = $backupDatabase->backupTables(TABLES, OUTPUT_DIR) ? 'CORRECTO' : 'KO';
echo "Resultado del Respaldo: ".$status;

/**
 * The Backup_Database class
 */
class Backup_Database {
    /**
     * Host where database is located
     */
    private $host = '';

    /**
     * Username used to connect to database
     */
    private $username = '';

    /**
     * Password used to connect to database
     */
    private $passwd = '';

    /**
     * Database to backup
     */
    private $dbName = '';

    /**
     * Database charset
     */
    private $charset = '';

    /**
     * Constructor initializes database
     */
    function Backup_Database($host, $username, $passwd, $dbName, $charset = 'utf8')
    {
        $this->host     = $host;
        $this->username = $username;
        $this->passwd   = $passwd;
        $this->dbName   = $dbName;
        $this->charset  = $charset;

        $db = new db();
        if ($db->connect()) {
            $this->db = $db;
        }

        $this->initializeDatabase();
    }

    protected function initializeDatabase()
    {
        /*if ($this->db->connect()) {
            print_r($this->db->select("SHOW TABLES;"));
        exit();
        }
        
        $conn = mysql_connect($this->host, $this->username, $this->passwd);
        mysql_select_db($this->dbName, $conn);
        /*if (! mysql_set_charset ($this->charset, $conn))
        {
            mysql_query('SET NAMES '.$this->charset);
        }*/
    }

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function backupTables($tables = '*', $outputDir = '.')
    {
        try
        {
            ini_set('memory_limit', '1204M');
            /**
            * Tables to export
            */
            if($tables == '*')
            {
                $tables = array();
                //$result = mysql_query('SHOW TABLES');
                $result = $this->db->select("SHOW TABLES;");

                //print_r($result);
                //exit();
                foreach ($result as $row) {
                    $tables[] = $row['Tables_in_sistema'];
                    //$tables[] = $row[0];
                }
                /*while($row = $result)
                {
                    print_r($row[0]) ."</br>";
                    $tables[] = $row[0];
                }*/

                //print_r($tables);
                //exit();
            }
            else
            {
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            $sql = 'CREATE DATABASE IF NOT EXISTS '.$this->dbName.";\n\n";
            $sql .= 'USE '.$this->dbName.";\n\n";

            /**
            * Iterate tables
            */
            foreach($tables as $table)
            {

                echo "Respaldando tabla... <b>".$table."</b>";

                $result = $this->db->select('SELECT * FROM ' . $table);

                $numFields = $this->db->field_count($result);

                $sql .= 'DROP TABLE IF EXISTS '.$table.';';
                //$row2 = mysql_fetch_row($this->db->select('SHOW CREATE TABLE '.$table));
                $row2 = $this->db->select('SHOW CREATE TABLE '.$table);
                //print_r($row2[0]['Create Table']);
                //exit();
                $sql.= "\n\n".$row2[0]['Create Table'].";\n\n";


                //for ($i = 0; $i < $numFields; $i++)
                //{
                    /*while($row = mysql_fetch_row($result))
                    {
                        $sql .= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j<$numFields; $j++)
                        {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                            if (isset($row[$j]))
                            {
                                $sql .= '"'.$row[$j].'"' ;
                            }
                            else
                            {
                                $sql.= '""';
                            }

                            if ($j < ($numFields-1))
                            {
                                $sql .= ',';
                            }
                        }

                        $sql.= ");\n";
                    }*/

                    foreach ($result as $key => $row) 
                    {
                        //print_r($row);
                        //exit();
                        $sql .= 'INSERT INTO '.$table.' VALUES(';

                        $cont=0;
                        foreach ($row as $value) {
                            $value = addslashes($value);
                            $value = str_replace("\n","",$value);
                            if (!empty($value) || $value != NULL) {
                                $sql .= '"' . $value . '"';
                            }else{
                                $sql .= '""';
                            }

                            if ( $cont < ($numFields-1)) {
                                $sql .= ',';
                            }

                            $cont++;
                               
                        }
                        
                        $sql.= ");\n";
                    }
                //}

                $sql.="\n\n\n";

                echo "    Estado -> Correcto <br>" . "";
            }
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }

        return $this->saveFile($sql, $outputDir);
    }

    /**
     * Save SQL to file
     * @param string $sql
     */
    protected function saveFile(&$sql, $outputDir = '.')
    {
        if ( !file_exists($outputDir) ) {
            mkdir($outputDir, 0777);
        }

        if (!$sql) return false;

        try
        {
            $handle = fopen($outputDir.'/db-backup-'.$this->dbName.'-'.date("Ymd-His", time()).'.sql','w+');
            fwrite($handle, $sql);
            fclose($handle);
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }

        return true;
    }
}
?>