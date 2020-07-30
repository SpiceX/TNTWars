<?php /** @noinspection PhpUnused */
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
namespace tntwars\event;

use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Config;
use tntwars\arena\Arena;
use tntwars\task\RespawnTask;
use tntwars\TNTWars;
use tntwars\utils\CustomExplosion;
use tntwars\utils\PluginUtils;
use function array_rand;
use function scandir;
use function str_replace;

class BehaviorMinigameEvent implements Listener
{
	/**
	 * BehaviorMinigameEvent constructor.
	 */
	public function __construct()
	{
		TNTWars::getInstance()->getServer()->getPluginManager()->registerEvents($this, TNTWars::getInstance());
	}

	/**
	 * @param PlayerMoveEvent $event
	 */
	public function onBehaviorVoid(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)) {
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$player->getName()])) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
						$spawnRed = explode(',', $arena->get('spawn_red'));
						$spawnBlue = explode(',', $arena->get('spawn_blue'));
						$posRed = new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]);
						if ($player->distance($posRed) < 25){
							$player->teleport(new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]));
						}
						if (TNTWars::getArena()->getStatus($name) === Arena::STATUS_INGAME && $player->getY() > ((float)$spawnBlue[1]) + 10){
							$player->teleport(new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]));
						}
					}

					if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$player->getName()])) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
						$spawnRed = explode(',', $arena->get('spawn_red'));
						$spawnBlue = explode(',', $arena->get('spawn_blue'));
						$posBlue = new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]);
						if ($player->distance($posBlue) < 15){
							$player->teleport(new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]));
						}
						if (TNTWars::getArena()->getStatus($name) === Arena::STATUS_INGAME && $player->getY() > ((float)$spawnRed[1]) + 10){
							$player->teleport(new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]));
						}
					}
					if ($player->y <= 2) {
						if ($player->getGamemode() === 3) {
							$player->teleport($event->getPlayer()->getLevel()->getSafeSpawn());
						} else {
							if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$player->getName()])) {
								$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
								$spawnBlue = explode(',', $arena->get('spawn_blue'));
								$player->teleport(new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]));
							}

							if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$player->getName()])) {
								$arena = new Config(TNTWars::getInstance()->getDataFolder() . $name . '.yml', Config::YAML);
								$spawnRed = explode(',', $arena->get('spawn_red'));
								$player->teleport(new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]));
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event)
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)) {
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getEntity()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					if ($event instanceof EntityDamageByEntityEvent) {
						$entity = $event->getDamager();
						$killer = $event->getDamager()->getOwningEntity();
						$victim = $event->getEntity();
						if ($entity instanceof PrimedTNT && $victim instanceof Player && $killer instanceof Player) {
							$entityTeam = $entity->namedtag->getTagValue("OwningTeam", StringTag::class);
							if (!$entity->namedtag->hasTag("OwningTeam")){
								$event->setCancelled(true);
								$event->setKnockBack(0.0);
							}
							if ($killer === $victim) {
								$event->setCancelled(true);
								$event->setKnockBack(0.0);
							}
							if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$victim->getName()]) && $entityTeam === "RED") {
								$event->setCancelled(true);
								$event->setKnockBack(0.0);
							}
							if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$victim->getName()]) && $entityTeam === "BLUE") {
								$event->setCancelled(true);
								$event->setKnockBack(0.0);
							}
							if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$victim->getName()]) && $entityTeam === "BLUE") {
								if ($event->getFinalDamage() > $victim->getHealth() || $victim->getHealth() < 2) {
									$event->setCancelled();
									$Bluescore = TNTWars::getData()->getBlueScore($name);
									$Bluenew = $Bluescore + 1;
									TNTWars::getData()->setBlueScore($name, $Bluenew);
									$RedScore = TNTWars::getData()->getRedScore($name);
									$Rednew = $RedScore - 1;
									TNTWars::getData()->setRedScore($name, $Rednew);
									$this->getMessage($name, $victim, $killer);
									$this->sendDeath($name, $victim);
									PluginUtils::playSound($killer, 'random.orb', 1, 1);
								}
							}
							if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$victim->getName()]) && $entityTeam === "RED") {
								if ($event->getFinalDamage() > $victim->getHealth() || $victim->getHealth() < 2) {
									$event->setCancelled();
									$Bluescore = TNTWars::getData()->getBlueScore($name);
									$Bluenew = $Bluescore - 1;
									TNTWars::getData()->setBlueScore($name, $Bluenew);
									$RedScore = TNTWars::getData()->getRedScore($name);
									$Rednew = $RedScore + 1;
									TNTWars::getData()->setRedScore($name, $Rednew);
									$this->getMessage($name, $victim, $killer);
									$this->sendDeath($name, $victim);
									PluginUtils::playSound($killer, 'random.orb', 1, 1);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param string $arena
	 * @param Player $entity
	 * @param Player $damager
	 */
	private function getMessage(string $arena, Player $entity, Player $damager): void
	{
		$messages = [
			'§6§6{ENTITY} §7has been killed by §6§6{DAMAGER}',
			'§6{ENTITY} §7visit the underworld thanks to §6{DAMAGER}',
			'§6{ENTITY} §7was busted by §6{DAMAGER}',
			'§6{ENTITY} §7exploded due to §6{DAMAGER}',
			'§6{ENTITY} §7could not bear the explosion of §6{DAMAGER}',
			'§6{ENTITY} §7lost one of their lives due to §6{DAMAGER}',
			'§6{ENTITY} §7was defeated by §6{DAMAGER}',
			'§6{ENTITY} §7lost his life thanks to §6{DAMAGER}',
			'§6{ENTITY} §7did not survive the tnt of §6{DAMAGER}',
			'§6{ENTITY} §7died because of his enemy §6{DAMAGER}'
		];
		$message = $messages[array_rand($messages)];
		$message = str_replace('{ENTITY}', $entity->getName(), $message);
		$message = str_replace('{DAMAGER}', $damager->getName(), $message);
		TNTWars::getArena()->broadcast($arena, '§7' . $message, Arena::BROADCAST_MESSAGE);
	}

	/**
	 * @param string $arena
	 * @param Player $entity
	 */
	private function sendDeath(string $arena, Player $entity): void
	{
		$entity->setGamemode(3);
		$entity->teleport($entity->getLevel()->getSafeSpawn());
		TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new RespawnTask($arena, $entity), 20);
	}

	/**
	 * @param ExplosionPrimeEvent $event
	 */
	public function onPrimed(ExplosionPrimeEvent $event)
	{
		if ($event->isCancelled() or !$event->getEntity() instanceof PrimedTNT) {
			return;
		}
		$event->setBlockBreaking(true);
		if (!$event->isCancelled()) {
			$explosion = new CustomExplosion($event->getEntity(), $event->getForce(), $event->getEntity());
			if ($event->isBlockBreaking()) {
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

}