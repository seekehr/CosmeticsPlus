<?php

declare(strict_types=1);

namespace seeker\cosmetics;

use pocketmine\entity\Human;
use pocketmine\entity\Rideable;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\BinaryStream;
use seeker\CosmeticsPlus;

class Vehicle extends Human implements Rideable
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

	/**
	 * @var bool
	 */
	private $isDriving = false;

	public function __construct(CosmeticsPlus $main, Player $player, Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
		$this->main = $main;
		$this->owner = $player;
	}

	//copied from JaxkDev vehicle :trollface:
	public function updateMotion(float $x, float $y): void{
		//				(1 if only one button, 0.7 if two)
		//+y = forward. (+1/+0.7)
		//-y = backward. (-1/-0.7)
		//+x = left (+1/+0.7)
		//-x = right (-1/-0.7)
		if($x != 0){
			if($x > 0) $this->yaw -= $x*1;
			if($x < 0) $this->yaw -= $x*1;
			$this->motion = $this->getDirectionVector();
		}

		if($y > 0){
			//forward
			$this->motion = $this->getDirectionVector()->multiply($y*1);
			$this->yaw = $this->owner->getYaw();// - turn based on players rotation
		} elseif ($y < 0){
			//reverse
			$this->motion = $this->getDirectionVector()->multiply($y*1);
		}
	}

	//copied from JaxkDev lol mb sorry :( im lazy
	protected function broadcastMovement(bool $teleport = false) : void{
		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->getOffsetPosition($this->getPosition());
		$pk->pitch = $this->getPitch();
		$pk->headYaw = $this->getYaw();
		$pk->yaw = $this->getYaw();
		$pk->mode = MovePlayerPacket::MODE_NORMAL;

		$this->getLevel()->broadcastPacketToViewers($this->getPosition(), $pk);
	}

	public function link(): bool
	{
		if($this->isDriving) return false;
		$this->owner->setGenericFlag(self::DATA_FLAG_RIDING, true);
		$this->owner->setGenericFlag(self::DATA_FLAG_SITTING, true);
		$this->owner->setGenericFlag(self::DATA_FLAG_WASD_CONTROLLED, true);
		$this->owner->getDataPropertyManager()->setVector3(self::DATA_RIDER_SEAT_POSITION, new Vector3(0, 1, 0));
		$this->setGenericFlag(self::DATA_FLAG_SADDLED, true);
		$this->isDriving = true;
		$packet = new SetActorLinkPacket();
		$packet->link = new EntityLink($this->getId(), $this->owner->getId(), EntityLink::TYPE_RIDER, true, true);
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $packet);
		return true;
	}

	/*public function unlink(): bool
	{
		if(!$this->isDriving) return true;
		$this->owner->setGenericFlag(self::DATA_FLAG_RIDING, false);
		$this->owner->setGenericFlag(self::DATA_FLAG_SITTING, false);
		$this->owner->setGenericFlag(self::DATA_FLAG_WASD_CONTROLLED, false);
		$this->setGenericFlag(self::DATA_FLAG_SADDLED, false);
		$this->broadcastLink(EntityLink::TYPE_REMOVE);
		$this->isDriving = null;
		return true;
	}*/

	public function sendSpawnPacket(Player $player): void
	{
		parent::sendSpawnPacket($player);
		$this->link();
	}

	public function entityBaseTick(int $tickDiff = -1): bool
	{
		if($this->isClosed() || $this->isFlaggedForDespawn()) return false;
		if(!$this->owner->isConnected()) $this->flagForDespawn();
		return parent::entityBaseTick($tickDiff);
	}

	public function getOwner() : Player
	{
		return $this->owner;
	}

	public function isDriving() : bool
	{
		return $this->isDriving;
	}

	public function canClimb(): bool
	{
		return true;
	}
}