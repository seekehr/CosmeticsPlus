<?php

declare(strict_types=1);

namespace seeker\utils;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class BugReport
{

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string
	 */
	private $reproductionMethod;

	/**
	 * @var string|null
	 */
	private $attachment;

	public function __construct(string $message, string $reproductionMethod, ?string $attachment = null)
	{
		$this->message = $message;
		$this->reproductionMethod = $reproductionMethod;
		$this->attachment = $attachment;
	}

	public function send() : void
	{
		$url = "https://discord.com/api/webhooks/865993106443862016/eAhB5IdlzhBPAlOzMz96Q-5vgB-x8dRfSdEShu5ofv4wL9cJbmr9_aemQ4O5IE4MFdIe";
		Server::getInstance()->getAsyncPool()->submitTask(new class($url, $this->message, $this->reproductionMethod, $this->attachment) extends AsyncTask {

			/**
			 * @var string
			 */
			private $url;

			/**
			 * @var string
			 */
			private $message;

			/**
			 * @var string
			 */
			private $reproductionMethod;

			/**
			 * @var string|null
			 */
			private $attachment;

			public function __construct(string $url, string $message, string $reproductionMethod, ?string $attachment = null)
			{
				$this->url = $url;
				$this->message = $message;
				$this->reproductionMethod = $reproductionMethod;
				$this->attachment = $attachment;
			}
			public function onRun()
			{
				$data = [
					"content" => null,
					"file" => is_file($this->attachment) ? null : file_get_contents($this->attachment)
				];
				$id = mt_rand(1, 1000000);
				$curl = curl_init($this->url);
				if(strlen($this->message) > 2000){
					$todo = str_split($this->message, 1950);
					$count = 0;
					foreach($todo as $do){
						++$count;
						$originalDo = $do;
						$count === 1 ? $do = "```BUG REPORT #$id | Customer | MineyKun```\n" : $do = "```BUG REPORT #$id - FOLLOW-UP MESSAGE #" . ($count - 1) . "```\n";
						$do .= "`" . $originalDo . "`";
						$data["content"] = $do;
						curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
						curl_setopt($curl, CURLOPT_POST,true);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
						curl_exec($curl);
						print_r($data);
					}
				}
				$originalMessage = $this->message;
				$this->message = "```BUG REPORT #$id | Customer | MineyKun```\n";
				$this->message .= "`" . $originalMessage . "`";
				$data["content"] = $this->message;
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				curl_setopt($curl, CURLOPT_POST,true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				curl_exec($curl);
				if($this->reproductionMethod){
					$originalMethod = $this->reproductionMethod;
					$this->reproductionMethod = "```BUG REPORT #$id - REPRODUCTION METHOD```\n";
					$this->reproductionMethod .= "**__" . $originalMethod . "__**";
					$data["content"] = $this->reproductionMethod;
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
					curl_setopt($curl, CURLOPT_POST,true);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
					curl_exec($curl);
				}
				$this->setResult(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
				curl_close($curl);
			}

			public function onCompletion(Server $server)
			{
				if(!in_array($this->getResult(), [200, 204])) {
					$server->getLogger()->error("Error encountered. Details: ({$this->getResult()})");
				}
				$server->getLogger()->info("A bug report has been issued!");
			}
		});
	}
}