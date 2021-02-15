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

	public function __construct(array $config, stdClass $data)
	{
		$this->_data = $data;
		$this->_config = $config;
		$this->run();
	}

	public function run() : void
	{
		if ($this->isUnnecessaryMessage()) {
			$this->deleteMessage();
		}
	}

	public function isUnnecessaryMessage() : bool
	{
		return isset($this->_data->message->left_chat_member) || isset($this->_data->message->new_chat_member);
	}

	public function deleteMessage() : void
	{
		$url = "https://api.telegram.org/bot" . $this->_config['tgBotToken'] . "/deleteMessage";
		$params = [
			'chat_id' => $this->_config['groupID'],
			'message_id' => $this->_data->message->message_id
		];
		$ch = curl_init();
		$data = http_build_query($params);
		$getUrl = $url."?".$data;
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $getUrl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);

		curl_exec($ch);
		curl_close($ch);
	}
}