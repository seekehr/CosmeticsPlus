<?php

declare(strict_types=1);

namespace seeker\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use seeker\CosmeticsPlus;

class DebugCommand extends PluginCommand
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	/**
	 * @var bool
	 */
	public $timingsStatus = false;

	public function __construct(string $name, Plugin $owner)
	{
		parent::__construct($name, $owner);
		$this->main = $owner;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if(!$sender->isOp()) return;
		if(!isset($args[0])){
			$sender->sendMessage(TextFormat::RED . "Insufficient arguments.");
			return;
		}
		switch($args[0]){
			case "timing":
			case "timings":
				if(!isset($args[1])){
					$sender->sendMessage(TextFormat::RED . "/debug timings <start|stop>");
					return;
				}
				if($args[1] == "start"){
					$this->timingsStatus = true;
					$this->main->getScheduler()->scheduleRepeatingTask(new class($this->main, $this, $sender) extends Task{

						/**
						 * @var CosmeticsPlus
						 */
						private $main;

						/**
						 * @var DebugCommand
						 */
						private $debugCommand;

						private $sender;

						private $countdown = 60;

						public function __construct(CosmeticsPlus $main, DebugCommand $debugCommand, $sender)
						{
							$this->main = $main;
							$this->debugCommand = $debugCommand;
							$this->sender = $sender;
						}

						public function onRun(int $currentTick)
						{
							if($this->countdown > 0) {
								$this->countdown--;
								if($this->countdown === 0){
									$this->sender->sendMessage(TextFormat::GREEN . "[Timer] " . TextFormat::BOLD . TextFormat::YELLOW . "Countdown done!");
								} else $this->sender->sendMessage(TextFormat::GREEN . "[Timer] " . TextFormat::BOLD . TextFormat::YELLOW . $this->countdown);
							}
							else $this->main->getScheduler()->cancelTask($this->getTaskId());
							if($this->debugCommand->timingsStatus === false) $this->main->getScheduler()->cancelTask($this->getTaskId());
						}
					}, 20);
					}
				if($args[1] == "stop") $this->timingsStatus = false;
				break;
		}
	}
}