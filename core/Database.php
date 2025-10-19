<?php
// core/Database.php
namespace Core;

use mysqli;

class Database {
	public static function connect(): mysqli {
		require __DIR__ . '/../config/config.php'; // defines $conn
		return $conn;
	}
}


