<?php 

namespace BeBride\DB;

use \BeBride\Model;

class Sql {

	const HOSTNAME = "localhost";
	const USERNAME = "root";
	const PASSWORD = "root";
	const DBNAME = "bebride_v01";


	private $conn;

	public function __construct()
	{
	
		try {
			$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
			Sql::USERNAME,
			Sql::PASSWORD,
			array(
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
			));
		} catch (\PDOException $e) {
			Model::setNotification('Connection failed: ' . $e->getMessage(),'error');
			echo 'Connection failed: ' . $e->getMessage();
			exit;
		}
			

	}

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

}

 ?>