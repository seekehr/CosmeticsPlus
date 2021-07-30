<?php

declare(strict_types=1);

namespace seeker\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use seeker\CosmeticsPlus;
use seeker\morphs\EndermanMorph;
use seeker\morphs\PigMorph;

class MorphsCommand extends PluginCommand
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	public function __construct(string $name, Plugin $owner)
	{
		parent::__construct($name, $owner);
		$this->main = $owner;
		$this->setDescription($this->main->getSettings()->get('morphs-command-description'));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if(!$sender instanceof Player){
			$sender->sendMessage($this->main->getSettings()->get('use-command-as-player-message'));
			return;
		}
		if(!isset($args[0])){
			$this->main->getFormManager()->sendMorphsForm($sender);
			return;
		}
		if(isset($args[0])){
			if($args[0] == 'off'){
				if(isset($this->main->morphs[strtolower($sender->getName())])){
					$entity = $sender->getLevel()->getEntity((int) $this->main->morphs[strtolower($sender->getName())]);
					$entity->flagForDespawn();
					unset($this->main->morphs[strtolower($sender->getName())]);
					$sender->setInvisible(false);
					$sender->sendMessage($this->main->getSettings()->get('turned-out-of-morph-message'));
				} else {
					$sender->sendMessage($this->main->getSettings()->get('morph-not-found-message'));
				}
			} elseif($args[0] == 'visibility'){
				if(!isset($args[1])){
					$sender->sendMessage(TextFormat::RED . "/" . $this->getName() . " visibility <on/off>");
					return;
				}
				if($args[1] == 'on' || $args[1] == 'true') $this->main->getDataProvider()->setPlayerMorphVisibility($sender, true);
				if($args[1] == 'off' || $args[1] == 'false') $this->main->getDataProvider()->setPlayerMorphVisibility($sender, false);
			}
			else $this->main->getFormManager()->sendMorphsForm($sender);
		}
	}
}