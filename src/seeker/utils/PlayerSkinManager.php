<?php

declare(strict_types=1);

namespace seeker\utils;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class PlayerSkinManager
{

	public static $skins = [];

	public static function loadAllSkins() : void
	{
		$dir = Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . "CosmeticsPlus" . DIRECTORY_SEPARATOR . "player_skins";
		if(!is_dir($dir)) return;
		foreach(scandir($dir) as $file){
			if($file === '.' || $file === '..' || is_dir($file)) continue;
			$info = explode('.', $file);
			$uncompressedData = gzuncompress(file_get_contents($dir . DIRECTORY_SEPARATOR . $file));
			$data = explode(',', $uncompressedData);
			self::$skins[$info[0]] = [$data[0], $data[1], $data[2], $data[3]];
		}
	}

	public static function savePlayerSkin(Player $player, Skin $skin) : void
	{
		if(!is_dir(Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . "CosmeticsPlus" . DIRECTORY_SEPARATOR . "player_skins")) mkdir(Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . "CosmeticsPlus" . DIRECTORY_SEPARATOR . "player_skins");
		Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData(), Server::getInstance()->getDataPath()) extends AsyncTask {

			/**
			 * @var string
			 */
			private $name;

			/**
			 * @var string
			 */
			private $skinData;

			/**
			 * @var string
			 */
			private $capeData;

			/**
			 * @var string
			 */
			private $geometryName;

			/**
			 * @var string
			 */
			private $geometryData;

			/**
			 * @var string
			 */
			private $path;

			public function __construct(string $name, string $skinData, string $capeData, string $geometryName, string $geometryData, string $path)
			{
				$this->name = $name;
				$this->skinData = $skinData;
				$this->capeData = $capeData;
				$this->geometryName = $geometryName;
				$this->geometryData = $geometryData;
				$this->path = $path;
			}

			public function onRun()
			{
				$file = fopen($this->path . "plugin_data" . DIRECTORY_SEPARATOR . "CosmeticsPlus" . DIRECTORY_SEPARATOR . "player_skins" . DIRECTORY_SEPARATOR . strtolower($this->name) . ".txt", 'w');
				$data = [$this->skinData, $this->capeData, $this->geometryName, $this->geometryData];
				fwrite($file, gzcompress(implode(',', $data)));
				fclose($file);
			}
		});
	}

	public static function getPlayerSkin(Player $player) : ?Skin
	{
		if(!isset(self::$skins[strtolower($player->getName())])) return null;
		return new Skin('player_skin', self::$skins[strtolower($player->getName())][0], self::$skins[strtolower($player->getName())][1], self::$skins[strtolower($player->getName())][2], self::$skins[strtolower($player->getName())][3]);
	}
}