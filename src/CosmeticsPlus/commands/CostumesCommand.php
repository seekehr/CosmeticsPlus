<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use seeker\CosmeticsPlus\CosmeticsPlus;

class CostumesCommand extends PluginCommand
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	public function __construct(string $name, Plugin $owner)
	{
		parent::__construct($name, $owner);
		$this->main = $owner;
		$this->setDescription($this->main->getSettings()->get('costumes-command-description'));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$sender instanceof Player ? $this->main->getFormManager()->sendCostumesForm($sender) : $sender->sendMessage($this->main->getSettings()->get('use-command-as-player-message'));
	}
}