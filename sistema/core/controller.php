<?php

require_once 'db.php';

/**
* La clase principal de la que deben heredar todos los controladore
*/
class controller
{
	/**
     * Este objeto permite acceso directo a la base de datos.
     * @var db
     */
    protected $db;

	public function __construct()
	{
		$this->db = new db();

		if ($this->db->connect()) {
			$this->private_core();
		}else{
			echo "¡Imposible conectar con la base de datos!";
		}
	}

	/**
     * Esta es la función principal que se ejecuta cuando el usuario ha hecho login
     */
    protected function private_core()
    {
        
    }
}