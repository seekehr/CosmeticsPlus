<?php

declare(strict_types=1);

namespace seeker\utils;

use pocketmine\block\Water;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\level\particle\WaterParticle;
use seeker\tasks\Spawn2DVerticleCircleTask;
use seeker\tasks\SpawnHeadParticleTask;
use seeker\tasks\SpawnHelixTask;

class ParticleManager
{

	private static $particles = [];

	const SHAPES = [
		'helix' => SpawnHelixTask::class,
		'2d_vertical_circle' => Spawn2DVerticleCircleTask::class,
		'head_particle' => SpawnHeadParticleTask::class
	];

	const PARTICLES = [
		'avp' => AngryVillagerParticle::class,
		'wp' => WaterParticle::class,
		'smp' => SmokeParticle::class,
		'fp' => FlameParticle::class,
		'dp' => DustParticle::class,
		'bp' => BubbleParticle::class,
		'cp' => CriticalParticle::class,
		'etp' => EnchantmentTableParticle::class,
		'hvp' => HappyVillagerParticle::class,
		'hp' => HeartParticle::class,
		'ip' => InkParticle::class,
		'ldp' => LavaDripParticle::class,
		'rp' => RedstoneParticle::class,
		'sp' => SnowballPoofParticle::class,
		'pp' => PortalParticle::class
	];

	public static function register(string $shape, string $name, string $particle, ?int $height = null) : bool
	{
		$shape = strtolower($shape);
		if(isset(self::$particles[$shape][strtolower($name)])) return false;
		if($shape !== 'helix' && $shape !== '2d_vertical_circle' && $shape !== 'head_particle') return false;
		if(!isset(self::PARTICLES[strtolower($particle)])) return false;
		self::$particles[$shape][strtolower($name)] = [$particle, $height];
		return true;
	}

	/**
	 * @param bool $include_shape
	 * @return array
	 */
	public static function getAllParticleNames(bool $include_full_info = false) : array
	{
		$shapes = ['helix', '2d_verticle_circle', 'head_particle'];
		$particles = [];
		foreach($shapes as $shape){
			foreach(self::getAllParticlesByShape($shape) as $particle => $info){
				$name = $particle;
				if($include_full_info){
					$more_info = self::get($shape, $name);
					$particles[] = [$shape, $name, $more_info[0], $more_info[1]];
				}
				else $particles[] = $name;
			}
		}
		return $particles;
	}

	/**
	 * @param string $shape
	 * @return array
	 */
	public static function getAllParticlesByShape(string $shape) : array
	{
		$particles = [];
		if(!isset(self::$particles[$shape])) return [];
		foreach(self::$particles[$shape] as $particle => $info) $particles[$particle] = $info;
		return $particles;
	}

	/**
	 * @param string $shape
	 * @param string $name
	 * @return array|null
	 */
	public static function get(string $shape, string $name) : ?array
	{
		return self::$particles[$shape][strtolower($name)] ?? null;
	}

	/**
	 * @param string $shape
	 * @param string $name
	 * @return bool
	 */
	public static function exists(string $shape, string $name) : bool
	{
		return isset(self::$particles[$shape][strtolower($name)]);
	}

	public static function getTaskByShape(string $shape)
	{
		return self::SHAPES[$shape] ?? null;
	}

	public static function getParticleByName(string $name)
	{
		return self::PARTICLES[$name] ?? null;
	}
}