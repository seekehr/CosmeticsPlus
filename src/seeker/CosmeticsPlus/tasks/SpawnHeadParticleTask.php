<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus\tasks;

use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use seeker\CosmeticsPlus\utils\ParticleManager;

class SpawnHeadParticleTask extends Task
{

	/**
	 * @var Player
	 */
	private $player;

	private $particle;

	/**
	 * @var Player[]
	 */
	private $viewers;

	/**
	 * @var int
	 */
	private $height;

	public function __construct(Player $player, string $particle, array $viewers, int $height = 0)
	{
		$this->player = $player;
		$this->particle = ParticleManager::getParticleByName($particle);
		$this->viewers = $viewers;
		$this->height = $height;
	}

	public function onRun(int $currentTick)
	{
		$level = $this->player->getLevel();
		$pos = $this->player->asVector3();
		$level->addParticle(new $this->particle(new Vector3($pos->x, $pos->y + $this->height, $pos->z)));
	}
}