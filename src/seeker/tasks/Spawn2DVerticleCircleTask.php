<?php

declare(strict_types=1);

namespace seeker\tasks;

use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use seeker\utils\ParticleManager;

class Spawn2DVerticleCircleTask extends Task
{

	/**
	 * @var Player
	 */
	private $player;

	/**
	 * @var string|null
	 */
	private $particle;

	/**
	 * @var Player[]
	 */
	private $viewers;

	/**
	 * @var int
	 */
	private $radius;

	public function __construct(Player $player, string $particle, array $viewers, int $radius = 0)
	{
		$this->player = $player;
		$this->particle = ParticleManager::getParticleByName($particle);
		$this->viewers = $viewers;
		$this->radius = $radius;
	}

	public function onRun(int $currentTick)
	{
		$r = $this->radius;
		$diff = 20;
		for($theta = 0; $theta <= 360; $theta += $diff){
			$x = $r * sin($theta);
			$y = $r * cos($theta);
			$this->player->getLevel()->addParticle(new $this->particle(new Vector3($this->player->getX() + $x, $this->player->getY() + $y, $this->player->getZ())));
		}
	}
}