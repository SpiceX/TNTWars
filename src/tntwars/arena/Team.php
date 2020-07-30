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
namespace tntwars\arena;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\Color;
use pocketmine\utils\Config;
use tntwars\TNTWars;
use function array_rand;

class Team
{
	const RED_TEAM = 0;
	const BLUE_TEAM = 1;
	const NULL_TEAM = 2;

	public function choose(string $arena): int
	{
		$teams = [self::BLUE_TEAM, self::RED_TEAM];
		if (TNTWars::getData()->getMaxPlayersBlueTeam($arena) === 0 && TNTWars::getData()->getMaxPlayersRedTeam($arena) === 0) {
			return $teams[array_rand($teams)];
		}

		if (TNTWars::getData()->getMaxPlayersBlueTeam($arena) > TNTWars::getData()->getMaxPlayersRedTeam($arena)) {
			return $teams[1];
		}

		if (TNTWars::getData()->getMaxPlayersRedTeam($arena) > TNTWars::getData()->getMaxPlayersBlueTeam($arena)) {
			return $teams[0];
		}

		if (TNTWars::getData()->getMaxPlayersBlueTeam($arena) === TNTWars::getData()->getMaxPlayersRedTeam($arena)) {
			return $teams[array_rand($teams)];
		}

		return $teams[array_rand($teams)];
	}

	public function addToTeam(string $arena, Player $player, int $team): void
	{
		switch ($team) {
			case self::BLUE_TEAM:
				TNTWars::getData()->setPlayerBlueTeam($arena, $player);
				break;
			case self::RED_TEAM:
				TNTWars::getData()->setPlayerRedTeam($arena, $player);
				break;
		}
		$this->sendValues($player, $team);
	}

	public function removeToTeam(string $arena, Player $player): void
	{
		TNTWars::getData()->removePlayerBlueTeam($arena, $player);
		TNTWars::getData()->removePlayerRedTeam($arena, $player);
	}


	private function sendValues(Player $player, int $team): void
	{
		switch ($team) {
			case self::BLUE_TEAM:
				$player->setNameTag('§l§9' . $player->getName());

				$cap = ItemFactory::get(Item::LEATHER_CAP);
				$tunic = ItemFactory::get(Item::LEATHER_TUNIC);
				$leggins = ItemFactory::get(Item::LEATHER_LEGGINGS);
				$boots = ItemFactory::get(Item::LEATHER_BOOTS);

				$color = (new Color(0, 0, 255))->toRGBA();//working
				$tag = new CompoundTag("", []);
				$tag->setInt("customColor", $color);

				$cap->setCompoundTag($tag);
				$tunic->setCompoundTag($tag);
				$leggins->setCompoundTag($tag);
				$boots->setCompoundTag($tag);

				$player->getArmorInventory()->setHelmet($cap);
				$player->getArmorInventory()->setChestplate($tunic);
				$player->getArmorInventory()->setLeggings($leggins);
				$player->getArmorInventory()->setBoots($boots);
				$player->getInventory()->setItem(0, $tunic);

				break;
			case self::RED_TEAM:
				$player->setNameTag('§l§c' . $player->getName());

				$cap = ItemFactory::get(Item::LEATHER_CAP);
				$tunic = ItemFactory::get(Item::LEATHER_TUNIC);
				$leggins = ItemFactory::get(Item::LEATHER_LEGGINGS);
				$boots = ItemFactory::get(Item::LEATHER_BOOTS);

				$color = 0xff00255;
				$tag = new CompoundTag("", []);
				$tag->setInt("customColor", $color);

				$cap->setCompoundTag($tag);
				$tunic->setCompoundTag($tag);
				$leggins->setCompoundTag($tag);
				$boots->setCompoundTag($tag);

				$player->getArmorInventory()->setHelmet($cap);
				$player->getArmorInventory()->setChestplate($tunic);
				$player->getArmorInventory()->setLeggings($leggins);
				$player->getArmorInventory()->setBoots($boots);
				$player->getInventory()->setItem(0, $tunic);
				break;
		}
	}

	public function teleportToSlot(string $arena): void
	{
		foreach (TNTWars::getInstance()->getServer()->getLevelByName(TNTWars::getArena()->getLevel($arena))->getPlayers() as $player) {
			if (isset(TNTWars::getData()->getPlayersBlueTeam($arena)[$player->getName()])) {
				$arenaConfig = new Config(TNTWars::getInstance()->getDataFolder() . $arena . '.yml', Config::YAML);
				$spawnBlue = explode(',', $arenaConfig->get('spawn_blue'));
				$player->teleport(new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]));
			}
			if (isset(TNTWars::getData()->getPlayersRedTeam($arena)[$player->getName()])) {
				$arenaConfig = new Config(TNTWars::getInstance()->getDataFolder() . $arena . '.yml', Config::YAML);
				$spawnRed = explode(',', $arenaConfig->get('spawn_red'));
				$player->teleport(new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]));
			}
		}
	}
}
