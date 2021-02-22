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
	const MODERATOR_ROLE = 1;

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

	public function isAddingNewTip(string $tip) : bool
	{
		$statement = $this->db->prepare("SELECT id FROM commands WHERE name = :name");
		$statement->bindParam(":name", $tip, PDO::PARAM_STR);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return is_array($row);
	}

	public function getCommand(string $command) : string
	{
		$statement = $this->db->prepare("SELECT id FROM commands WHERE name = :name");
		$statement->bindParam(":name", $command, PDO::PARAM_STR);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return $row['id'];
	}

	public function addNewTip(string $tip, int $author_id) : bool
	{
		$arr = explode('-', $tip);
		$command = $this->getCommand($arr[0]);
		$sql = "INSERT INTO tips (name, body, author_id, created, status, command_id) VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([$arr[1], $arr[2], $author_id, time(), 0, $command]);
		return true;
	}

	public function getLastTip() : array
	{
		$sql = "SELECT tips.`id`, tips.`name`, tips.`body`, tips.`author_id`, commands.`name` as command,
				users.`fio`, users.`tg_username` FROM tips LEFT JOIN commands ON tips.command_id = commands.id
				LEFT JOIN users	ON tips.author_id = users.telegram_id LIMIT 1";
		$statement = $this->db->prepare($sql);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return $row;
	}

	public function getModerators() : array
	{
		$statement = $this->db->prepare("SELECT telegram_id FROM users WHERE role_id = " . self::MODERATOR_ROLE);
		$statement->execute();
		$rows = $statement->fetch(PDO::FETCH_ASSOC);
		return $rows;
	}
}