<?php 
	class databaseManager{
		
		var $databaseAddress;
		var $username;
		var $password;
		var $newlink = false; // whether or not to establish a new connection on subseq req
		
		var $connection;
		
		function __construct($databaseAddress, $username, $password, $connect = false, $newlink = false)
		{
			$this->databaseAddress = $databaseAddress;
			$this->username = $username;
			$this->password = $password;
			$this->newlink = $newlink;
			
			if($connect){
				$this->connect();
			}
		}
		
		function setDatabase($databaseName)
		{
			if(!mysqli_select_db($databaseName, $this->connection)) {
				die("Could not set database to $databaseName: " . mysqli_error());
			}
		}
		
		function connect()
		{
			$connection = mysqli_connect( $this->databaseAddress, $this->username, $this->password, $this->newlink);
			
			if (!$connection) {
				die('Could not connect: ' . mysqli_error());
			};
			
			$this->connection = $connection;
		}
		
		function disconnect()
		{
			mysqli_close($connection);
		}
		
		// Execute a query. Preferable to use doPreparedStatement for
		// security purposes.
		function doQuery($sql)
		{
			if(!$result = mysqli_query($sql)) {
				die('Error running query: ' . mysqli_error());
			};
						
			return($result);
		}

		// Execute prepared statement and return result as array of keyed-arrays
		function doPreparedStatement($preparedStatement, $bindParameters) {
			if ($stmt = $connection->prepare($preparedStatement)) {

				/* bind parameters for markers */
				foreach($bindParameters as $parm=>$val) {
					$stmt->bind_param($parm, $val);
				}

				/* execute query */
				$stmt->execute();
				$result = $stmt->get_result();

				$resultArray = [];
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$resultArray[] = $row;
				}

				/* close statement */
				$stmt->close();

				return $resultArray;
			} else {
				die('Error creating prepared statement: ' . mysqli_error());
			}
		}
	}
?>