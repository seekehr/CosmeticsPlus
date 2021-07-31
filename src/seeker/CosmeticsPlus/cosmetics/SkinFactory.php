<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus\cosmetics;

use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;

class SkinFactory
{

	/**
	 * @var <string, Skin>
	 */
	private static $data = [];

	/**
	 * @param string $type
	 * @param string $name
	 * @param Skin $skin
	 * @return bool
	 */
	public static function register(string $type, string $name, Skin $skin) : bool
	{
		if(!self::meetsRequirements($skin)) return false;
		if($type !== strtolower('facemask') && $type !== strtolower('grave') && $type !== strtolower('rideable') && $type !== strtolower('vehicle') && $type !== strtolower('costume')) return false;
		if(isset(self::$data[$type][$name])) return false;
		self::$data[$type][strtolower($name)] = $skin;
		return true;
	}

	/**
	 * @param string $type
	 * @return string[]
	 */
	public static function getAllSkinsByType(string $type) : array
	{
		$skins = [];
		if(!isset(self::$data[$type])) return [];
		foreach(self::$data[$type] as $skin) $skins[] = $skin;
		return $skins;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @return Skin|null
	 */
	public static function get(string $type, string $name) : ?Skin
	{
		return self::$data[$type][strtolower($name)] ?? null;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @return bool
	 */
	public static function exists(string $type, string $name) : bool
	{
		return self::$data[$type][strtolower($name)] !== null;
	}

	/**
	 * @param Skin $skin
	 * @return bool
	 */
	private static function meetsRequirements(Skin $skin) : bool
	{
		try {
			$skin->validate();
		} catch(InvalidSkinException $exception){
			return false;
		}
		if($skin->getCapeData() !== "" || $skin->getGeometryName() === '' || is_null($skin->getGeometryData())) return false;
		return true;
	}
}