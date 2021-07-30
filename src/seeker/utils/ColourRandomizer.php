<?php

declare(strict_types=1);

namespace seeker\utils;

use pocketmine\utils\TextFormat;

class ColourRandomizer
{

	public function randomize(string $string) : string
	{
		$available_format = [
			TextFormat::WHITE,
			TextFormat::DARK_RED,
			TextFormat::RED,
			TextFormat::DARK_BLUE,
			TextFormat::BLUE,
			TextFormat::DARK_PURPLE,
			TextFormat::LIGHT_PURPLE,
			TextFormat::GOLD,
			TextFormat::YELLOW,
			TextFormat::DARK_AQUA,
			TextFormat::AQUA,
			TextFormat::DARK_GREEN,
			TextFormat::GREEN,
			TextFormat::DARK_GRAY,
			TextFormat::GRAY,
			TextFormat::BLACK
		];
		$string = $available_format[array_rand($available_format)] . $string;
		return $string;
	}
}