<?php

declare(strict_types=1);

namespace seeker\morphs;

use pocketmine\entity\Creature;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use seeker\CosmeticsPlus;

class IronGolemMorph extends Creature
{

	use Morph;

	const NETWORK_ID = self::IRON_GOLEM;

	public $height = 2.9;
	public $width = 1.4;

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	/** @var Player */
	private $owner;

	public function __construct(Level $level, CompoundTag $nbt, CosmeticsPlus $main, Player $player)
	{
		parent::__construct($level, $nbt);
		$this->main = $main;
		$this->owner = $player;
		$this->setCanSaveWithChunk(false);
	}

	public function entityBaseTick(int $tickDiff = 1): bool
	{
		if($this->owner->isClosed()) $this->flagForDespawn();
		if($this->isClosed()){
			$this->owner->setInvisible(false);
			return false;
		}
		foreach($this->getViewers() as $viewer){
			if(!$this->main->getDataProvider()->canSeeMorphs($viewer)){
				$this->setInvisible();
				$this->owner->setInvisible(false);
			}
		}
		$this->setHealth($this->getMaxHealth());
		$this->setPosition($this->owner->getPosition()->asVector3());
		$this->setRotation($this->owner->getYaw(), $this->owner->getPitch());
		return parent::entityBaseTick($tickDiff);
	}

	public function getName(): string
	{
		return "IronGolemMorph";
	}
}