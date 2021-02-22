<?php

/*
 * @author Erkin Pardayev
 * @link https://pardayev.uz
 * @access public
 * @version 0.0.1
 * @see
 */

namespace Pardayev\botHelper;

use stdClass;

class Main
{
	private $_data;
	private $_config;
	private $_db;

	public function __construct(array $config, stdClass $data)
	{
		$this->_data = $data;
		$this->_config = $config;
		$this->_db = new Database();
		$this->run();
	}

	public function run() : void
	{
		if ($this->isUnnecessaryMessage()) {
			$this->deleteMessage();
		}
		if (isset($this->_data->message)) {
			$this->progressTextMessage();
		}
	}

	public function isUnnecessaryMessage() : bool
	{
		return isset($this->_data->message->left_chat_member) || isset($this->_data->message->new_chat_member);
	}

	public function deleteMessage() : void
	{
		$params = [
			'chat_id' => $this->_config['groupID'],
			'message_id' => $this->_data->message->message_id
		];
		$this->sendRequest("deleteMessage", $params);
	}

	public function progressTextMessage() : void
	{
		if ($this->_db->isNewUser($this->_data->message->from->id)) {
			$this->_db->registerUser($this->_data);
			$this->greetings($this->_data->message->from->id);
		}
	}

	public function greetings(int $tgUserID) : void
	{
		$commands = $this->_db->getAviableCommands();
		$msg = "Salom. Linuxiston community botiga xush kelibsiz!\n";
		$msg .= "Hozirda mavjud to'plamlar ro'yhati: \n";
		$i = 1;
		foreach ($commands as $command) {
			$msg .= $i . ". " .$command['name'] . "\n";
		}
		$msg .= "Biror bir mavjud to'plamga yangi ma'lumot qo'shmoqchi bo'lsangiz quyidagicha amal bajaring.\n";
		$msg .= "............\n";
		$msg .= "faq-ls-Linux terminalda biror bir katalogdagi fayllar ro'yhatini ko'rsatadi\n";
		$msg .= "............\n";
		$msg .= "Bu yerda\n";
		$msg .= "------------\n";
		$msg .= "faq - mavjud to'plam nomi.\n";
		$msg .= "ls - buyruq nomi.\n";
		$msg .= "keyingi qism buyruqni izoxi.\n";

		$params = [
			'chat_id' => $tgUserID,
			'text' => $msg,
			'parse_mode' => 'Markdown'
		];
		$this->sendRequest("sendMessage", $params);
	}

	public function sendRequest(string $method, array $params) : void
	{
		$url = "https://api.telegram.org/bot" . $this->_config['tgBotToken'] . "/" .$method;
		$data = http_build_query($params);
		$url = $url."?".$data;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);

		curl_exec($ch);
		curl_close($ch);
	}

}