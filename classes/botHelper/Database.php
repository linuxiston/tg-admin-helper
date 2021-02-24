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

	public function getAvailableCommands() : array
	{
		$statement = $this->db->prepare("SELECT name FROM commands WHERE status = " . self::STATUS_ACTIVE);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function isAddingNewTip(string $tip) : bool
	{
		$statement = $this->db->prepare("SELECT id FROM commands WHERE name = :name");
		$statement->bindParam(":name", $tip);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return is_array($row);
	}

	public function getCommand(string $command) : string
	{
		$statement = $this->db->prepare("SELECT id FROM commands WHERE name = :name");
		$statement->bindParam(":name", $command);
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
				LEFT JOIN users	ON tips.author_id = users.telegram_id ORDER BY tips.id DESC LIMIT 1";
		$statement = $this->db->prepare($sql);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	public function getModerators() : array
	{
		$statement = $this->db->prepare("SELECT telegram_id FROM users WHERE role_id = " . self::MODERATOR_ROLE);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	public function acceptTip(int $tipID) : void
	{
		$statement = $this->db->prepare("UPDATE tips SET status = 1 WHERE id = " . $tipID);
		$statement->execute();
	}

	public function removeTip(int $tipID) : void
	{
		$statement = $this->db->prepare("DELETE FROM tips 1 WHERE id = " . $tipID);
		$statement->execute();
	}

	public function getTips(string $command) : array
	{
		$tip = str_replace('/', '', $command);
		$sql = "SELECT `tips`.`name`, `tips`.`body` FROM `commands`";
    	$sql .= " LEFT JOIN `tips` ON `commands`.`id` = `tips`.`command_id` WHERE `tips`.`status` = 1";
	    $sql .= " AND `commands`.`name` = :tip";
		$statement = $this->db->prepare($sql);
		$statement->bindParam(":tip", $tip);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function isAdmin(int $userID, $role = self::MODERATOR_ROLE) : bool
	{
		$statement = $this->db->prepare("SELECT id FROM users WHERE role_id = :role AND telegram_id = :tgID");
		$statement->bindParam(":tgID", $userID, PDO::PARAM_INT);
		$statement->bindParam(":role", $role, PDO::PARAM_INT);
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		return !is_bool($row);
	}
}