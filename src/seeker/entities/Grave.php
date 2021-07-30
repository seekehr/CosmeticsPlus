<?php

declare(strict_types=1);

namespace seeker\entities;

use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;
use seeker\CosmeticsPlus;

class Grave extends Human
{

	use EntityTrait;

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	public $height = 0;
	public $width = 0;
	public $canCollide = false;
	protected $gravity = 0;
	protected $drag = false;
	/**
	 * @var Player
	 */
	private $victim;

	public function __construct(CosmeticsPlus $main, Player $player, Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
		$this->main = $main;
		$this->victim = $player;
		$this->setCanSaveWithChunk(false);
		$this->sendSkin(Server::getInstance()->getOnlinePlayers());
	}

	public function sendSpawnPacket(Player $player): void
	{
		parent::sendSpawnPacket($player);
		$finalizedNametag = str_replace("{player}", $this->victim->getName(), $this->main->getSettings()->get("grave-nametag"));
		$this->setNameTag($finalizedNametag);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function entityBaseTick(int $tickDiff = 1): bool
	{
		if($this->closed || $this->isFlaggedForDespawn()) return false;
		if($this->ticksLived === (int) $this->main->getSettings()->get("grave-despawn-cooldown") * 20) $this->flagForDespawn();
		return parent::entityBaseTick($tickDiff);
	}
}