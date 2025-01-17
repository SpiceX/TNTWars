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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Fireworks extends Item {

	public const TYPE_SMALL_SPHERE = 0;
	public const TYPE_HUGE_SPHERE = 1;
	public const TYPE_STAR = 2;
	public const TYPE_CREEPER_HEAD = 3;
	public const TYPE_BURST = 4;

	//color = chr(dye metadata)
	public const COLOR_BLACK = "\x00";
	public const COLOR_RED = "\x01";
	public const COLOR_DARK_GREEN = "\x02";
	public const COLOR_BROWN = "\x03";
	public const COLOR_BLUE = "\x04";
	public const COLOR_DARK_PURPLE = "\x05";
	public const COLOR_DARK_AQUA = "\x06";
	public const COLOR_GRAY = "\x07";
	public const COLOR_DARK_GRAY = "\x08";
	public const COLOR_PINK = "\x09";
	public const COLOR_GREEN = "\x0a";
	public const COLOR_YELLOW = "\x0b";
	public const COLOR_LIGHT_AQUA = "\x0c";
	public const COLOR_DARK_PINK = "\x0d";
	public const COLOR_GOLD = "\x0e";
	public const COLOR_WHITE = "\x0f";

	public function __construct(int $meta = 0) {
		parent::__construct(self::FIREWORKS, $meta, "Fireworks");
	}

	public function getFlightDuration(): int {
		return $this->getExplosionsTag()->getByte("Flight", 1);
	}

	public function getRandomizedFlightDuration(): int {
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	public function setFlightDuration(int $duration): void {
		$tag = $this->getExplosionsTag();
		$tag->setByte("Flight", $duration);
		$this->setNamedTagEntry($tag);
	}

	public function addExplosion(int $type, string $color, string $fade = "", bool $flicker = false, bool $trail = false): void
	{
		$explosion = new CompoundTag();
		$explosion->setByte("FireworkType", $type);
		$explosion->setByteArray("FireworkColor", $color);
		$explosion->setByteArray("FireworkFade", $fade);
		$explosion->setByte("FireworkFlicker", $flicker ? 1 : 0);
		$explosion->setByte("FireworkTrail", $trail ? 1 : 0);

		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag("Explosions") ?? new ListTag("Explosions");
		$explosions->push($explosion);
		$tag->setTag($explosions);
		$this->setNamedTagEntry($tag);
	}

	protected function getExplosionsTag(): CompoundTag {
		return $this->getNamedTag()->getCompoundTag("Fireworks") ?? new CompoundTag("Fireworks");
	}

	public function getRandomColor(): string
	{
		$availableColors = $this->getAvailableColors();
		return $availableColors[array_rand($this->getAvailableColors())];
	}

	public function getAvailableColors(): array
	{
		return [
			self::COLOR_BLACK,
			self::COLOR_RED,
			self::COLOR_DARK_GREEN,
			self::COLOR_BROWN,
			self::COLOR_BLUE,
			self::COLOR_DARK_PURPLE,
			self::COLOR_DARK_AQUA,
			self::COLOR_GRAY,
			self::COLOR_DARK_GRAY,
			self::COLOR_PINK,
			self::COLOR_GREEN,
			self::COLOR_YELLOW,
			self::COLOR_LIGHT_AQUA,
			self::COLOR_DARK_PINK,
			self::COLOR_GOLD,
			self::COLOR_WHITE
		];
	}

	public function getAvailableTypes(){
		return [
			self::TYPE_STAR,
			self::TYPE_SMALL_SPHERE,
			self::TYPE_HUGE_SPHERE,
			self::TYPE_CREEPER_HEAD,
			self::TYPE_BURST
		];
	}

	public function getRandomType(){
		$availableTypes = $this->getAvailableTypes();
		return $availableTypes[array_rand($this->getAvailableTypes())];
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool {
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);

		$entity = Entity::createEntity("FireworksRocket", $player->getLevel(), $nbt, $this);

		if($entity instanceof Entity) {
			--$this->count;
			$entity->spawnToAll();
			return true;
		}
		return false;
	}
}