<?php

declare(strict_types=1);

namespace seeker\tasks;

use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use seeker\utils\ParticleManager;

class SpawnHelixTask extends Task
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

	/**
	 * @var double
	 */
	private $t = 0, $r = 1;

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
		$this->t += M_PI / 16;
		$x = $this->r * cos($this->t);
		$y = 0.5 * $this->t;
		$z = $this->r * sin($this->t);
		$level->addParticle(new $this->particle($pos->add($x, $y, $z)));
		$pos->subtract($x, $y, $z);
		if($pos->add($x, $y, $z)->y > $this->player->getY() + $this->height){
			$this->t = 0;
		}
	}
}