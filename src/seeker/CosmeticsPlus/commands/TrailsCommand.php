<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use seeker\CosmeticsPlus\CosmeticsPlus;

class TrailsCommand extends PluginCommand
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	public function __construct(string $name, Plugin $owner)
	{
		parent::__construct($name, $owner);
		$this->main = $owner;
		$this->setDescription($this->main->getSettings()->get('trails-command-description'));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if(!$sender instanceof Player) {
			$this->main->getServer()->getLogger()->info($this->main->getSettings()->get("use-command-as-player-message"));
			return;
		}
		if(!isset($args[0]) || count($args) > 2) {
			$this->main->getFormManager()->sendTrailsForm($sender);
		}
		if(isset($args[0])) { //lol why am i even doing this
			if($args[0] == 'help'){
				$sender->sendMessage(TextFormat::GREEN . "/" . $this->getName() . " [optional:<help, off, visibility>] (if visibility: <on/off>)");
			}
			elseif($args[0] == 'off' || $args[0] == 'false') {
				$this->main->getDataProvider()->setPlayerMovingTrail($sender, '');
				$sender->sendMessage(TextFormat::RED . "Toggled off trails.");
			} elseif ($args[0] == 'visibility') {
				if(!isset($args[1])) {
					$sender->sendMessage(TextFormat::RED . "/" . $this->getName() . " visibility <on/off>");
					return;
				}
				if($args[1] == 'on' || $args[1] == 'true') {
					$this->main->getDataProvider()->setPlayerTrailVisibility($sender, true);
					$sender->sendMessage(TextFormat::GREEN . "Toggled on trails visibility.");
				}
				if($args[1] == 'off' || $args[1] == 'false') {
					$this->main->getDataProvider()->setPlayerTrailVisibility($sender, false);
					$sender->sendMessage(TextFormat::RED . "Toggled off trails visibility.");
				}
			} else $this->main->getFormManager()->sendTrailsForm($sender);
		}
	}
}