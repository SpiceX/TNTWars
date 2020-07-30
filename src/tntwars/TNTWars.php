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
namespace tntwars;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use RuntimeException;
use tntwars\arena\Arena;
use tntwars\arena\MapReset;
use tntwars\arena\Team;
use tntwars\bossbar\BossBar;
use tntwars\data\ArenaData;
use tntwars\entities\GameEntity;
use tntwars\event\EventManager;
use tntwars\scoreboard\ScoreboardStore;
use tntwars\task\WaitingLobbyTask;
use tntwars\utils\Fireworks;
use tntwars\utils\FireworksRocket;
use tntwars\utils\PluginUtils;

/**
 * @class TNTWars
 * Credits to @Josewowgame for his libminigame
 */
class TNTWars extends PluginBase implements Listener
{
	/** @var TNTWars */
	private static $main;
	/** @var Team */
	private static $team;
	/** @var Arena */
	private static $arena;
	/** @var ArenaData */
	private static $data;
	/** @var BossBar */
	private static $boss;
	/** @var array */
	public static $handle = [];
	/** @var PluginUtils */
	private static $pluginUtils;
	/** @var MapReset */
	private static $mapReset;
	/** @var ScoreboardStore */
	private $scoreboardStore;

	public function onEnable(): void
	{
		self::$main = $this;
		self::$team = new Team();
		self::$arena = new Arena();
		self::$data = new ArenaData();
		self::$boss = new BossBar();
		self::$pluginUtils = new PluginUtils();
		self::getArena()->ini();
		EventManager::ini();
		Entity::registerEntity(GameEntity::class, true);
		$this->getScheduler()->scheduleRepeatingTask(new WaitingLobbyTask(), 15);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		ItemFactory::registerItem(new Fireworks(), true);
		Item::initCreativeItems();
		if(!Entity::registerEntity(FireworksRocket::class, false, ["FireworksRocket"])) {
			$this->getLogger()->error("Failed to register FireworksRocket entity with savename 'FireworksRocket'");
		}
		@mkdir($this->getDataFolder() . 'saves');
		$this->getLogger()->info("§aPlugin made by @LiTEK_");
		$this->getLogger()->info("§6LICENSE: §eAA3DH-0PHD1-0803P-X4Z7V-PGHR4");
	}

	public function onLoad(){
		$this->scoreboardStore = new ScoreboardStore();
		self::$mapReset = new MapReset($this);
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onDamage(EntityDamageByEntityEvent $event){
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		if ($damager instanceof Player && $entity instanceof GameEntity){
			$event->setCancelled(true);
			$damager->sendMessage('§aLooking for an available TNTWars game...');
			if (!self::getArena()->join($damager)) {
				$damager->sendMessage('§cConnection error when trying to find an available game');
			}
		}
	}

	/**
	 * @return static
	 */
	public static function getInstance(): self
	{
		if (self::$main === null) {
			throw new RuntimeException('TNTWars Error> Instance TNTWars.php is null!');
		}
		return self::$main;
	}

	/**
	 * @return Team
	 */
	public static function getTeam(): Team
	{
		if (self::$team === null) {
			throw new RuntimeException('TNTWars Error> Instance Team.php is null');
		}
		return self::$team;
	}

	/**
	 * @return Arena
	 */
	public static function getArena(): Arena
	{
		if (self::$arena === null) {
			throw new RuntimeException('TNTWars Error> Instance Arena.php is null');
		}
		return self::$arena;
	}

	/**
	 * @return ArenaData
	 */
	public static function getData(): ArenaData
	{
		if (self::$data === null) {
			throw new RuntimeException('TNTWars Error> Instance ArenaData.php is null');
		}
		return self::$data;
	}

	/**
	 * @return BossBar
	 */
	public static function getBossBar(): BossBar
	{
		if (self::$boss === null) {
			throw new RuntimeException('TNTWars Error> Instance BossBar.php is null');
		}
		return self::$boss;
	}

	/**
	 * @return ScoreboardStore
	 */
	public function getStore(): ScoreboardStore
	{
		return $this->scoreboardStore;
	}

	/**
	 * @return PluginUtils
	 */
	public static function getPluginUtils(): PluginUtils
	{
		return self::$pluginUtils;
	}

	/**
	 * @return MapReset
	 */
	public static function getMapReset(): MapReset
	{
		return self::$mapReset;
	}
}
