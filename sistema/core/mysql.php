<?php

class mysql
{
    private $host_db;
    private $user_db;
    private $pass_db;
    private $db_name;
    /**
     * El enlace con la base de datos.
     * @var resource
     */
    protected static $link;

    public function __construct()
    {
        $archivo = $_SERVER['DOCUMENT_ROOT'].'/sistema/config/parametros.xml';
        if (file_exists($archivo)) {
            $xml  = simplexml_load_file($archivo);
            $this->host_db = $xml->host_db;
            $this->user_db = $xml->user_db;
            $this->pass_db = $xml->pass_db;
            $this->db_name = $xml->db_name;
        }
    }

    /**
     * Conecta a la base de datos.
     * @return boolean
     */
    public function connect()
    {
        $connected = FALSE;

        if (self::$link) {
            $connected = TRUE;
        } else if (class_exists('mysqli')) {
            self::$link = @new mysqli($this->host_db, $this->user_db, $this->pass_db, $this->db_name, intval('3306'));

            if (self::$link->connect_error) {
                //self::$core_log->new_error(self::$link->connect_error);
                self::$link = NULL;
            } else {
                self::$link->set_charset('utf8');
                $connected = TRUE;

                /// desactivamos el autocommit
                self::$link->autocommit(FALSE);
            }
        } else {
            //self::$core_log->new_error('No tienes instalada la extensión de PHP para MySQL.');
            echo "No tienes instalada la extensión de PHP para MySQL.";
        }

        return $connected;
    }


    /**
    * Desconecta de la base de datos.
    * @return boolean
    */
    public function close()
    {
      if(self::$link)
      {
         $retorno = self::$link->close();
         self::$link = NULL;
         return $retorno;
      }
      else
         return TRUE;
    }
    
    /**
     * Ejecuta una sentencia SQL de tipo select, y devuelve un array con los resultados,
     * o false en caso de fallo.
     * @param string $sql
     * @return array
     */
    public function select($sql)
    {
        $result = FALSE;

        if (self::$link) {
            
            $aux = self::$link->query($sql);
            if ($aux) {
                $result = array();
                while ($row = $aux->fetch_array(MYSQLI_ASSOC)) {
                    $result[] = $row;
                }
                $aux->free();
            } else {
                /// añadimos el error a la lista de errores
                echo self::$link->error;
            }
        }

        return $result;
    }

    /**
     * Ejecuta una sentencia SQL de tipo select, pero con paginación,
     * y devuelve un array con los resultados,
     * o false en caso de fallo.
     * Limit es el número de elementos que quieres que devuelve.
     * Offset es el número de resultado desde el que quieres que empiece.
     * @param string $sql
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function select_limit($sql, $limit = 30, $offset = 0)
    {
        $result = FALSE;

        if (self::$link) {
            /// añadimos limit y offset a la consulta sql
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';

            $aux = self::$link->query($sql);
            if ($aux) {
                $result = array();
                while ($row = $aux->fetch_array(MYSQLI_ASSOC)) {
                    $result[] = $row;
                }
                $aux->free();
            } else {
                /// añadimos el error a la lista de errores
                echo self::$link->error;
            }

        }

        return $result;
    }

    /**
     * Ejecuta sentencias SQL sobre la base de datos (inserts, updates y deletes).
     * Para selects, mejor usar las funciones select() o select_limit().
     * Por defecto se inicia una transacción, se ejecutan las consultas, y si todo
     * sale bien, se guarda, sino se deshace.
     * Se puede evitar este modo de transacción si se pone false
     * en el parametro transaction.
     * @param string $sql
     * @param boolean $transaction
     * @return boolean
     */
    public function exec($sql, $transaction = TRUE)
    {
        $result = FALSE;

        if (self::$link) {
  
            if ($transaction) {
                $this->begin_transaction();
            }

            $i = 0;
            if (self::$link->multi_query($sql)) {
                do {
                    $i++;
                } while (self::$link->more_results() && self::$link->next_result());
            }

            if (self::$link->errno) {
                echo 'Error al ejecutar la consulta ' . $i . ': ' . self::$link->error;
            } else {
                $result = TRUE;
            }

            if ($transaction) {
                if ($result) {
                    $this->commit();
                } else {
                    $this->rollback();
                }
            }
        }

        return $result;
    }

    /**
     * Inicia una transacción SQL.
     * @return boolean
     */
    public function begin_transaction()
    {
        if (self::$link) {
            /**
             * Ejecutamos START TRANSACTION en lugar de begin_transaction()
             * para mayor compatibilidad.
             */
            return self::$link->query("START TRANSACTION;");
        }

        return FALSE;
    }

    /**
     * Guarda los cambios de una transacción SQL.
     * @return boolean
     */
    public function commit()
    {
        if (self::$link) {
            /// aumentamos el contador de selects realizados
            //self::$t_transactions++;

            return self::$link->commit();
        }

        return FALSE;
    }

    /**
     * Deshace los cambios de una transacción SQL.
     * @return boolean
     */
    public function rollback()
    {
        if (self::$link) {
            return self::$link->rollback();
        }

        return FALSE;
    }

    /**
     * Devuleve el último ID asignado al hacer un INSERT en la base de datos.
     * @return integer|false
     */
    public function lastval()
    {
        $aux = $this->select('SELECT LAST_INSERT_ID() as num;');
        if ($aux) {
            return $aux[0]['num'];
        }

        return FALSE;
    }


    /**
     * Escapa las comillas de la cadena de texto.
     * @param string $str
     * @return string
     */
    public function escape_string($str)
    {
        if (self::$link) {
            return self::$link->escape_string($str);
        }

        return $str;
    }

    /**
     * Devuelve el estilo de fecha del motor de base de datos.
     * @return string
     */
    public function date_style()
    {
        return 'Y-m-d';
    }

    /**
     * Devuelve un array con los nombres de las tablas de la base de datos.
     * @return array
     */
    public function list_tables()
    {
        $tables = array();

        $aux = $this->select("SHOW TABLES;");

        if ($aux) {
            foreach ($aux as $a) {
                if (isset($a['Tables_in_' . $this->db_name])) {
                    $tables[] = array('name' => $a['Tables_in_' . $this->db_name]);
                }
            }
        }

        return $tables;
    }

    /**
     * .
     * @return 
     */
    public function field_count()
    {
        if (self::$link) {
            return self::$link->field_count;
        }
        return FALSE;
    }
}