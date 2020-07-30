<?php
/**
 * Copyright 2020-2022 LiTEK
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);
namespace tntwars\utils;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function explode;
use function floor;
use function max;
use function round;
use function rtrim;
use function str_pad;
use function str_split;
use function substr_count;
use function trim;
use function usort;

/**
 * @class PluginUtils
 * Thanks to '@Josewowgame' for the padding methods
 */
class PluginUtils
{

	public const charWidth = 6;

	public const spaceChar = ' ';

	public const charWidths = [
		' ' => 4,
		'!' => 2,
		'"' => 5,
		'\'' => 3,
		'(' => 5,
		')' => 5,
		'*' => 5,
		',' => 2,
		'.' => 2,
		':' => 2,
		';' => 2,
		'<' => 5,
		'>' => 5,
		'@' => 7,
		'I' => 4,
		'[' => 4,
		']' => 4,
		'f' => 5,
		'i' => 2,
		'k' => 5,
		'l' => 3,
		't' => 4,
		'' => 5,
		'|' => 2,
		'~' => 7,
		'█' => 9,
		'░' => 8,
		'▒' => 9,
		'▓' => 9,
		'▌' => 5,
		'─' => 9
	];
	private static $coolDown;

	/**
	 * @param string $input
	 * @param int $maxLength
	 * @param bool $addRightPadding
	 * @return string
	 */
	public static function center(string $input, int $maxLength = 0, bool $addRightPadding = false): string
	{
		$lines = explode("\n", trim($input));

		$sortedLines = $lines;
		usort($sortedLines, static function (string $a, string $b) {
			return self::getPixelLength($b) <=> self::getPixelLength($a);
		});

		$longest = $sortedLines[0];

		if ($maxLength === 0) {
			$maxLength = self::getPixelLength($longest);
		}

		$result = '';

		$spaceWidth = self::getCharWidth(self::spaceChar);

		foreach ($lines as $sortedLine) {
			$len = max($maxLength - self::getPixelLength($sortedLine), 0);
			$padding = (int)round($len / (2 * $spaceWidth));
			$paddingRight = (int)floor($len / (2 * $spaceWidth));
			$result .= str_pad(self::spaceChar, $padding) . $sortedLine . ($addRightPadding ? str_pad(self::spaceChar, $paddingRight) : '') . "\n";
		}

		$result = rtrim($result, "\n");

		return $result;
	}

	/**
	 * @param string $line
	 * @return int
	 */
	public static function getPixelLength(string $line): int
	{
		$length = 0;
		foreach (str_split(TextFormat::clean($line)) as $c) {
			$length += self::getCharWidth($c);
		}
		$length += substr_count($line, TextFormat::BOLD);
		return $length;
	}

	/**
	 * @param string $c
	 * @return int
	 */
	private static function getCharWidth(string $c): int
	{
		return self::charWidths[$c] ?? self::charWidth;
	}

	/**
	 * @param Player $player
	 * @param string $soundName
	 * @param float|int $volume
	 * @param float|int $pitch
	 */
	public static function playSound(Player $player, string $soundName, float $volume = 0, float $pitch = 0): void
	{
		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->x = (int)$player->x;
		$pk->y = (int)$player->y;
		$pk->z = (int)$player->z;
		$pk->volume = $volume;
		$pk->pitch = $pitch;
		$player->dataPacket($pk);
	}

	/**
	 * @param Player $player
	 * @param int $seconds
	 * @return bool
	 */
	public function checkCooldown(Player $player, $seconds = 5): bool {
		if(!isset(self::$coolDown[$player->getName()])){
			self::$coolDown[$player->getName()] = time();
			return false;
		}
		if(((time() - self::$coolDown[$player->getName()]) <= $seconds)){
			return true;
		}
		self::$coolDown[$player->getName()] = time();
		return false;
	}
}
