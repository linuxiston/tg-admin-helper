<?php


namespace Pardayev\botHelper;


use PDO;
use PDOException;
use stdClass;

class Database
{
	private $_host = "localhost";
	private $_user = "root";
	private $_pass = "root";
	private $_name = "helper";
	private $db;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;

	public function __construct()
	{
		$dsn = "mysql:host=" . $this->_host . ";dbname="  .$this->_name;
		try {
			$this->db = new PDO($dsn, $this->_user, $this->_pass);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage(), (int)$e->getCode());
		}
	}

	public function isNewUser(int $tgUserID) : bool
	{
		$statement = $this->db->prepare("SELECT id FROM users WHERE telegram_id = :tgID");
		$statement->bindParam(":tgID", $tgUserID, PDO::PARAM_INT);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return !$row;
	}

	public function registerUser(stdClass $data) : void
	{
		$tgUserID = $data->message->from->id;
		$from = $data->message->from->first_name;
		$from = $from . " " . $data->message->from->last_name;
		$tgUserName = $data->message->from->username;

		$sql = "INSERT INTO users (telegram_id, fio, tg_username, role_id, started) VALUES (?,?,?,?,?)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([$tgUserID, $from, $tgUserName, null, time()]);
	}

	public function getAviableCommands() : array
	{
		$statement = $this->db->prepare("SELECT name FROM commands WHERE status = " . self::STATUS_ACTIVE);
		$statement->execute();
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $rows;
	}
}