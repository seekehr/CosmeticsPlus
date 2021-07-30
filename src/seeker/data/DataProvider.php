<?php

declare(strict_types=1);

namespace seeker\data;

use pocketmine\Player;

interface DataProvider
{

	public function openConnection(): void;

	public function isRegisteredPlayer(string $name): bool;

	public function registerPlayer(Player $player): void;

	public function getPlayerData(string $name): ?array;

	public function setPlayerMovingTrail(Player $player, string $trail): void;

	public function getPlayerMovingTrail(Player $player): ?string;

	public function setPlayerTrailVisibility(Player $player, bool $visibility): void;

	public function canSeeTrails(Player $player): bool;

	public function setPlayerMorphVisibility(Player $player, bool $visibility): void;

	public function canSeeMorphs(Player $player): bool;

	public function setPlayerParticle(Player $player, string $shape, string $name): void;

	public function getPlayerParticle(Player $player): ?array;

	public function setPlayerParticleVisibility(Player $player, bool $visibility): void;

	public function canSeeParticles(Player $player): bool;

	public function setFaceMask(Player $player, string $mask): void;

	public function getPlayerFaceMask(Player $player): ?string;

	public function setPlayerCostume(Player $player, string $costume) : void;

	public function getPlayerCostume(Player $player) : ?string;

	public function setPlayerGrave(Player $player, string $grave): void;

	public function getPlayerGrave(Player $player): string;

	public function saveData(string $name): void;

	public function close(): void;
}