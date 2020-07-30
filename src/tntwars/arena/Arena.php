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

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use tntwars\commands\CreatorCommand;
use tntwars\commands\PlayerCommand;
use tntwars\scoreboard\Scoreboard;
use tntwars\task\CelebrationTask;
use tntwars\task\DefaultAnimation;
use tntwars\TNTWars;
use tntwars\utils\PluginUtils;
use function array_flip;
use function array_keys;
use function closedir;
use function count;
use function file_exists;
use function is_dir;
use function max;
use function mkdir;
use function opendir;
use function readdir;
use function scandir;
use function str_replace;

class Arena
{
	const STATUS_ENABLE = 'on';
	const STATUS_INGAME = 'ingame';

	const BROADCAST_MESSAGE = 0;
	const BROADCAST_POPUP = 1;
	const BROADCAST_SOUND = 2;
	const BROADCAST_BOSSBAR = 5;
	const BROADCAST_SCOREBOARD = 6;
	const BROADCAST_TITLE = 7;

	const MODE_BUILDER = 3;
	const MODE_BATTLE = 4;

	/** @var Scoreboard */
	private $scoreboard;

	/**
	 * Initialize class
	 */
	public function ini(): void
	{
		if (!file_exists(TNTWars::getInstance()->getDataFolder())) {
			@mkdir(TNTWars::getInstance()->getDataFolder());
		}
		TNTWars::getInstance()->getServer()->getCommandMap()->register('tw', new CreatorCommand(TNTWars::getInstance()));
		TNTWars::getInstance()->getServer()->getCommandMap()->register('tntwars', new PlayerCommand(TNTWars::getInstance()));
		$this->loadAll();
		TNTWars::getInstance()->getServer()->getLogger()->info('§a[TNTWars] Have been found (' . $this->getMaxArenas() . ') arena(s) have been loaded');
		$this->scoreboard = new Scoreboard(TNTWars::getInstance(), "§l§6TNT§cWars", Scoreboard::ACTION_CREATE);
		$this->scoreboard->create(Scoreboard::DISPLAY_MODE_SIDEBAR, Scoreboard::SORT_DESCENDING, true);
		$this->scoreboard = new Scoreboard(TNTWars::getInstance(), "§l§6TNT§cWars", Scoreboard::ACTION_MODIFY);
	}

	public function loadAll(): void
	{
		if ($this->getMaxArenas() > 0) {
			if (empty(TNTWars::getInstance()->getDataFolder())) {
				return;
			}
			$scan = scandir(TNTWars::getInstance()->getDataFolder());
			foreach ($scan as $files) {
				if ($files !== '..' && $files !== '.') {
					if (!is_file( TNTWars::getInstance()->getDataFolder() . $files)) {
						continue;
					}
					$name = str_replace('.yml', '', $files);
					$this->load($name);
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getMaxArenas(): int
	{
		$dir = TNTWars::getInstance()->getDataFolder();
		$files = 0;
		if (is_dir($dir) && $gd = opendir($dir)) {
			while (($handle = readdir($gd)) !== false) {
				$files++;
			}
			closedir($gd);
		}
		return $files - 3;
	}

	/**
	 * @param string $name
	 */
	public function load(string $name): void
	{
		TNTWars::getMapReset()->loadMap($name);
		if (!TNTWars::getInstance()->getServer()->isLevelLoaded($this->getLevel($name))) {
			TNTWars::getInstance()->getServer()->loadLevel($this->getLevel($name));
		}
		$level = TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($name));
		$level->setTime(0);
		$level->stopTime();
		$this->setStatus($name, self::STATUS_ENABLE);
		TNTWars::getData()->add($name);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getLevel(string $name): string
	{
		$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
		return $arena->get('level');
	}

	/**
	 * @param string $name
	 * @param string $status
	 */
	public function setStatus(string $name, string $status): void
	{
		$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
		$arena->set('status', $status);
		$arena->save();
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function join(Player $player): bool
	{
		$games = [];
		if ($this->getMaxArenas() > 0) {
			if (empty(TNTWars::getInstance()->getDataFolder())) {
				return false;
			}
			$scan = scandir(TNTWars::getInstance()->getDataFolder());
			foreach ($scan as $files) {
				if ($files !== '..' && $files !== '.') {
					if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
						continue;
					}
					$name = str_replace('.yml', '', $files);
					if ($this->getStatus($name) === self::STATUS_ENABLE && $this->getPlaying($name) < 8) {
						$games[$name] = $this->getPlaying($name);
					}
				}
			}
			if (count($games) !== 0) {
				$need = max($games);

				if (!($need)) {
					$need = mt_rand(0, count($games) - 1);
					$this->joinGame($player, array_keys($games)[$need]);
				} else {
					$index = array_flip($games);
					$need2 = $index[max($games)];
					$this->joinGame($player, $need2);
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getStatus(string $name): string
	{
		$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
		return $arena->get('status');
	}

	/**
	 * @param string $name
	 * @return int
	 */
	public function getPlaying(string $name): int
	{
		$number = 0;
		$level = TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($name));
		if (!Server::getInstance()->isLevelLoaded($name)){
			Server::getInstance()->loadLevel($name);
		}
		foreach ($level->getPlayers() as $player) {
			if (TNTWars::getData()->isPlaying($name, $player)) {
				$number++;
			}
		}
		return $number;
	}

	/**
	 * @param Player $player
	 * @param string $name
	 */
	private function joinGame(Player $player, string $name): void
	{
		if (!TNTWars::getData()->isPlaying($name, $player)) {
			if (is_file(TNTWars::getInstance()->getDataFolder() . $name . '.yml')) {
				$player->teleport(TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($name))->getSafeSpawn());
				$player->getInventory()->clearAll();
				$player->getArmorInventory()->clearAll();
				$player->setHealth(20);
				$player->setFood(20);
				$player->setGamemode(2);
				$this->broadcast($name, '§8' . $player->getName() . ' §7joined the game (' . ($this->getPlaying($name) + 1) . '/8)', self::BROADCAST_MESSAGE);
				$this->broadcast($name, 'random.levelup', self::BROADCAST_SOUND);
				TNTWars::getTeam()->addToTeam($name, $player, TNTWars::getTeam()->choose($name));
				$player->getInventory()->setItem(8, Item::get(Item::REDSTONE, 0, 1)->setCustomName('§6Exit game'));
				TNTWars::getBossBar()->showTo($player);
			}
		}
	}

	/**
	 * @param string $arena
	 * @param string $value
	 * @param int $type
	 */
	public function broadcast(string $arena, string $value, int $type): void
	{
		switch ($type) {
			case self::BROADCAST_TITLE:
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					$player->sendTitle($value);
				}
				break;
			case self::BROADCAST_MESSAGE:
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					$player->sendMessage($value);
				}
				break;
			case self::BROADCAST_POPUP:
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					$player->sendPopup($value);
				}
				break;
			case self::BROADCAST_SOUND:
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					PluginUtils::playSound($player, $value, 1, 1);
				}
				break;
			case self::BROADCAST_BOSSBAR:
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					TNTWars::getBossBar()->setTitleFrom($player, "\n\n" . $value);
				}
				break;
			case self::BROADCAST_SCOREBOARD:
				$this->scoreboard->removeLines();
				$this->scoreboard->setLine(2, "§b" . gmdate('d/m/Y'));
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					$this->scoreboard->setLine(3, "§eMap:§7 " . TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getFolderName());
					$this->scoreboard->setLine(4, "§ePlayer: §7" . $player->getName());
					$this->scoreboard->setLine(6, "§6pixellive.sytes.net:19155");
					$this->scoreboard->showTo($player);
				}
		}
	}

	/**
	 * @return int
	 */
	public function getTotalPlaying(): int
	{
		$number = 0;
		if ($this->getMaxArenas() > 0) {
			if (empty(TNTWars::getInstance()->getDataFolder())) {
				return 0;
			}
			$scan = scandir(TNTWars::getInstance()->getDataFolder());
			foreach ($scan as $files) {
				if ($files !== '..' && $files !== '.') {
					if (!is_file(TNTWars::getInstance()->getDataFolder() .$files)) {
						continue;
					}
					$name = str_replace('.yml', '', $files);
					foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($name))->getPlayers() as $player) {
						if (TNTWars::getData()->isPlaying($name, $player)) {
							$number++;
						}
					}
				}
			}
		}
		return $number;
	}

	/**
	 * @param string $arena
	 * @param int $mode
	 */
	public function sendMode(string $arena, int $mode): void
	{
		foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
			switch ($mode) {
				case self::MODE_BUILDER:
					$player->getInventory()->clearAll();
					if (isset(TNTWars::getData()->getPlayersBlueTeam($arena)[$player->getName()])) {
						$player->getInventory()->setItem(4, Item::get(Block::WOOL, 11, 25));
					}
					if (isset(TNTWars::getData()->getPlayersRedTeam($arena)[$player->getName()])) {
						$player->getInventory()->setItem(4, Item::get(Block::WOOL, 14, 25));
					}
					break;
				case self::MODE_BATTLE:
					$player->getInventory()->clearAll();
					$player->getInventory()->setItem(4, Item::get(Item::TNT, 0, 1));
					break;
			}
		}
	}

	/**
	 * @param string $arena
	 * @param int $task
	 * @param int $time
	 */
	public function getPossibleMethodsOfWinning(string $arena, int $task, int $time): void
	{

		if (TNTWars::getData()->getRedScore($arena) === 50) {
			$this->sendWinning($arena, Team::RED_TEAM, $task);
		}

		if (TNTWars::getData()->getBlueScore($arena) === 50) {
			$this->sendWinning($arena, Team::BLUE_TEAM, $task);
		}

		if (TNTWars::getData()->getMaxPlayersBlueTeam($arena) === 0 && TNTWars::getData()->getMaxPlayersRedTeam($arena) > 0) {
			$this->sendWinning($arena, Team::RED_TEAM, $task);
		}

		if (TNTWars::getData()->getMaxPlayersRedTeam($arena) === 0 && TNTWars::getData()->getMaxPlayersBlueTeam($arena) > 0) {
			$this->sendWinning($arena, Team::BLUE_TEAM, $task);
		}
		if (TNTWars::getData()->getMaxPlayersRedTeam($arena) === 0 && TNTWars::getData()->getMaxPlayersBlueTeam($arena) === 0) {
			$this->sendWinning($arena, Team::NULL_TEAM, $task);
		}

		if ($this->getPlaying($arena) === 0) {
			$this->sendWinning($arena, Team::NULL_TEAM, $task);
		}

		if ($time === 60 * 20) {
			if (TNTWars::getData()->getRedScore($arena) > TNTWars::getData()->getBlueScore($arena)) {
				$this->sendWinning($arena, Team::RED_TEAM, $task);
			}
			if (TNTWars::getData()->getRedScore($arena) < TNTWars::getData()->getBlueScore($arena)) {
				$this->sendWinning($arena, Team::BLUE_TEAM, $task);
			}
			if (TNTWars::getData()->getRedScore($arena) === TNTWars::getData()->getBlueScore($arena)) {
				$this->sendWinning($arena, Team::NULL_TEAM, $task);
			}
		}
	}

	/**
	 * @param string $arena
	 * @param int $team
	 * @param int $task
	 */
	private function sendWinning(string $arena, int $team, int $task): void
	{
		switch ($team) {
			case Team::BLUE_TEAM:
				TNTWars::getInstance()->getScheduler()->cancelTask($task);
				TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new CelebrationTask($arena), 15);
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					if (!isset(TNTWars::getData()->getPlayersBlueTeam($arena)[$player->getName()])) {
						$player->setGamemode(3);
						$player->sendTitle('§4Game Over', '§9Blue §awin', 2);
					} else {
						$player->sendTitle('§6Victory', '§7You won the game', 2);
						TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new DefaultAnimation($player), 15);
					}
				}
				break;
			case Team::RED_TEAM:
				TNTWars::getInstance()->getScheduler()->cancelTask($task);
				TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new CelebrationTask($arena), 15);
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					if (!isset(TNTWars::getData()->getPlayersRedTeam($arena)[$player->getName()])) {
						$player->setGamemode(3);
						$player->sendTitle('§4Game Over', '§cRed §awin', 2);
					} else {
						$player->sendTitle('§6Victory', '§7You won the game', 2);
						TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new DefaultAnimation($player), 15);
					}
				}
				break;
			case Team::NULL_TEAM:
				TNTWars::getInstance()->getScheduler()->cancelTask($task);
				foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
					$player->sendTitle('§cGame Over', '§a', 2);
					$this->quit($arena, $player);
				}
				$this->load($arena);
				break;
		}
	}

	/**
	 * @param string $name
	 * @param Player $player
	 */
	public function quit(string $name, Player $player): void
	{
		$player->teleport(TNTWars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		$player->setGamemode(2);
		$player->setHealth(20);
		$player->setFood(20);
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->setNameTag($player->getName());
		TNTWars::getTeam()->removeToTeam($name, $player);
		TNTWars::getBossBar()->hideFrom($player);
		$this->scoreboard->hideFrom($player);
	}

}
