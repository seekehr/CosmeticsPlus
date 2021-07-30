<?php

declare(strict_types=1);

namespace seeker\data;

use pocketmine\Player;
use seeker\cosmetics\SkinFactory;
use seeker\CosmeticsPlus;

class JSONDataProvider implements DataProvider
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	/**
	 * A small note: at first, I was using arrays in a getter function's scope, but then I realized it was inefficient to keep loading it into memory, so ta-da! A property!
	 * @var array
	 */
	private $data = [];

	public function __construct(CosmeticsPlus $main)
	{
		$this->main = $main;
		$this->openConnection();
	}

	public function openConnection(): void
	{
		//do nothing, this is intended for sql data providers
		if(!is_dir($this->main->getDataFolder() . "player_data")) mkdir($this->main->getDataFolder() . "player_data");
		foreach(scandir($this->main->getDataFolder() . "player_data") as $file){
			if($file === '.' || $file === '..') continue;
			$explode = explode($file, ".");
			$this->data[$explode[0]] = json_decode(file_get_contents($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . $file), true);
		}
	}

	public function isRegisteredPlayer(string $name) : bool
	{
		return is_file($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($name) . ".json");
	}

	public function registerPlayer(Player $player): void
	{
		if($this->isRegisteredPlayer($player->getName())){
			$this->data[strtolower($player->getName())] = json_decode(file_get_contents($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($player->getName()) . ".json"), true);
			return;
		}
		$file = fopen($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($player->getName()) . ".json", 'wb');
		$data = [
			"trail" => null,
			"particle" => [null, null],
			"morph" => null,
			"facemask" => null,
			"costume" => null,
			"see-morphs" => true,
			"see-costume" => true,
			"grave" => 'default'
		];
		$content = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
		file_put_contents($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($player->getName()) . ".json", $content);
		fclose($file);
		$this->data[strtolower($player->getName())] = json_decode(file_get_contents($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($player->getName()) . ".json"), true);
	}

	public function getPlayerData(string $name): ?array
	{
		return $this->data[strtolower($name)];
	}

	public function setPlayerMovingTrail(Player $player, string $trail): void
	{
		$this->data[strtolower($player->getName())]["trail"] = $trail;
	}

	public function getPlayerMovingTrail(Player $player): ?string
	{
		return $this->data[strtolower($player->getName())]["trail"];
	}

	public function setPlayerTrailVisibility(Player $player, bool $visibility): void
	{
		$this->data[strtolower($player->getName())]["see-trails"] = $visibility;
	}

	public function canSeeTrails(Player $player) : bool
	{
		return (bool) $this->data[strtolower($player->getName())]["see-trails"];
	}

	public function setPlayerMorphVisibility(Player $player, bool $visibility) : void
	{
		$this->data[strtolower($player->getName())]["see-morphs"] = $visibility;
	}

	public function canSeeMorphs(Player $player) : bool
	{
		return (bool) $this->data[strtolower($player->getName())]["see-morphs"];
	}

	public function setPlayerParticle(Player $player, string $shape, string $name): void
	{
		$this->data[strtolower($player->getName())]["particle"] = [$shape, $name];
	}

	public function getPlayerParticle(Player $player): ?array
	{
		return $this->data[strtolower($player->getName())]["particle"];
	}

	public function setPlayerParticleVisibility(Player $player, bool $visibility): void
	{
		$this->data[strtolower($player->getName())]["see-particles"] = $visibility;
	}

	public function canSeeParticles(Player $player): bool
	{
		return (bool) $this->data[strtolower($player->getName())]["see-particles"];
	}

	public function setFaceMask(Player $player, string $mask): void
	{
		$this->data[strtolower($player->getName())]["facemask"] = strtolower($mask);
	}

	public function getPlayerFaceMask(Player $player): ?string
	{
		return $this->data[strtolower($player->getName())]["facemask"];
	}

	public function setPlayerCostume(Player $player, string $costume) : void
	{
		$this->data[strtolower($player->getName())] = $costume;
	}

	public function getPlayerCostume(Player $player) : ?string
	{
		return $this->data[strtolower($player->getName())]["costume"];
	}

	public function setPlayerGrave(Player $player, string $grave): void
	{
		$this->data[strtolower($player->getName())]["grave"] = $grave;
	}

	public function getPlayerGrave(Player $player): string
	{
		return $this->data[strtolower($player->getName())]["grave"];
	}

	public function saveData(string $name): void
	{
		file_put_contents($this->main->getDataFolder() . "player_data" . DIRECTORY_SEPARATOR . strtolower($name) . ".json", json_encode($this->data[strtolower($name)], JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
		unset($this->data[strtolower($name)]); //can't have useless shit in our memory, eh?
	}

	public function close(): void
	{
		// TODO: Implement close() method.
	}
}