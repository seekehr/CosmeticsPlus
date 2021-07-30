<?php

declare(strict_types=1);

namespace seeker\cosmetics;

use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\Server;
use seeker\CosmeticsPlus;

class FaceMask extends Human
{
	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	public $width = 0;
	public $height = 0;
	public $canCollide = false;
	protected $gravity = 0;
	protected $drag = false;

	/**
	 * @var Player
	 */
	private $owner;

	public function __construct(CosmeticsPlus $main, Player $player, Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
		$this->main = $main;
		$this->owner = $player;
	}

	public function sendSpawnPacket(Player $player): void
	{
		parent::sendSpawnPacket($player);
		$this->setNameTagVisible(false);
		$this->setNameTagAlwaysVisible(false);
		$packet = new SetActorLinkPacket();
		$packet->link = new EntityLink($this->owner->getId(), $this->getId(), EntityLink::TYPE_PASSENGER, true, false);
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $packet);
	}

	public function entityBaseTick(int $tickDiff = -1): bool
	{
		if($this->isClosed() || $this->isFlaggedForDespawn()) return false;
		if(!$this->owner->isConnected()) $this->flagForDespawn();
		$this->setPositionAndRotation($this->owner->asVector3(), $this->owner->getYaw(), $this->owner->getPitch());
		return parent::entityBaseTick($tickDiff);
	}
}