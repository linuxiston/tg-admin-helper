<?php

/*
 * @author Erkin Pardayev
 * @link https://pardayev.uz
 * @access public
 * @version 0.0.1
 * @see https://github.com/Linuxiston/tg-admin-helper
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

		if (isset($this->_data->message->entities)) {
			$this->progressCommand();
		}

		if (isset($this->_data->message)) {
			$this->progressTextMessage();
		}
		if (isset($this->_data->callback_query)) {
			$this->progressCallbackData($this->_data->callback_query);
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

	public function progressCommand() : void
	{
		if ($this->_db->isNewUser($this->_data->message->from->id)) {
			$this->_db->registerUser($this->_data);
			$this->greetings($this->_data->message->from->id);
		}
		if ($this->_db->isAdmin($this->_data->message->from->id)) {
			$tips = $this->_db->getTips($this->_data->message->text);
			$msg = "";
			foreach ($tips as $tip) {
				$msg .= "*" . $tip['name'] . "* - " . $tip['body'] . "\n";
			}
			$params = [
				'chat_id' => $this->_data->message->from->id,
				'text' => $msg,
				'parse_mode' => 'Markdown'
			];
			$this->sendRequest("sendMessage", $params);
		} else {
			var_dump(1);
		}
		exit();
	}

	public function progressCallbackData(stdClass $data)
	{
		$arr = explode(':', $data->data);
		if ($arr[0] == 'tips') {
			if ($arr[2] == 'accept') {
				$this->_db->acceptTip($arr[1]);
			} else {
				$this->_db->removeTip($arr[1]);
			}
		}
	}

	public function progressTextMessage() : void
	{
		$userID = $this->_data->message->from->id;
		$message = $this->_data->message->text;

		$arr = explode('-', $message);
		if (count($arr) > 2) {
			if ($this->_db->isAddingNewTip($arr[0])) {
				$this->_db->addNewTip($message, $userID);
				$msg = "Yangi ma'lumot qo'shganingiz uchun rahmat,";
				$msg .= " qo'shilgan ma'lumot moderatorlar tomonidan tasdiqlanganidan keyin ro'yhatga qo'shiladi";
				$tipData = $this->_db->getLastTip();
				$this->alertModerators($tipData);
			} else {
				$msg = "Ma'lumot qo'shish uchun bunday to'plam topilmadi.";
			}
			$params = [
				'chat_id' => $userID,
				'text' => $msg
			];
			$this->sendRequest("sendMessage", $params);
		}
	}

	public function alertModerators(array $tipData) : void
	{
		$msg = "Yangi buyruq qo'shildi, iltimos tasdiqlang yoki o'chirib yuboring\n";
		$msg .= "Bo'lim: " . $tipData['command'] . "\n";
		$msg .= "Nomi: " . $tipData['name'] . "\n";
		$msg .= "Izoxi: " . $tipData['body'] . "\n";
		$msg .= "Muallif: " . $tipData['fio'] . "\n";
		$msg .= "Link @" . $tipData['tg_username'] . "\n";
		$keyboard = [
			'inline_keyboard' => [
				[
					[
						'text' => 'Tasdiqlash',
						'callback_data' => 'tips:' . $tipData['id'] . ':accept'
					],
					[
						'text' => 'Bekor qilish',
						'callback_data' => 'tips:' . $tipData['id'] . ':remove'
					]
				]
			]
		];
		$moderators = $this->_db->getModerators();
		foreach ($moderators as $moderator) {
			$params = [
				'chat_id' => $moderator,
				'text' => $msg,
				'reply_markup' => json_encode($keyboard)
			];
			$this->sendRequest('sendMessage', $params);
		}
	}

	public function greetings(int $tgUserID) : void
	{
		$commands = $this->_db->getAvailableCommands();
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