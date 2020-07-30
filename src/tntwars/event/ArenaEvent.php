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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use tntwars\arena\Arena;
use tntwars\TNTWars;
use function scandir;
use function str_replace;

class ArenaEvent implements Listener
{
	/** @var array */
	public $throwedTNT = [];

	/**
	 * ArenaEvent constructor.
	 */
	public function __construct()
	{
		TNTWars::getInstance()->getServer()->getPluginManager()->registerEvents($this, TNTWars::getInstance());
	}

	/**
	 * @param Player $player
	 */
	public function addToThrowedList(Player $player){
		if (!$this->inThrowedList($player)){
			$this->throwedTNT[$player->getName()] = 1;
		}
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function inThrowedList(Player $player): bool
	{
		foreach (array_keys($this->throwedTNT) as $array_key) {
			if ($array_key === $player->getName()){
				return true;
			}
		}
		return in_array($player->getName(), $this->throwedTNT);
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onMenu(PlayerInteractEvent $event): void
	{

		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					if ($event->getItem()->getId() === Item::REDSTONE && $event->getItem()->getCustomName() === '§6Exit game') {
						TNTWars::getArena()->quit($name, $event->getPlayer());
					}
				}
			}
		}
	}

	/**
	 * @param PlayerDropItemEvent $event
	 */
	public function onDrop(PlayerDropItemEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					$event->setCancelled(true);
				}
			}
		}
	}


	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					if (TNTWars::getArena()->getStatus($name) === Arena::STATUS_INGAME && $event->getBlock()->getId() === Block::WOOL) {
						$event->setCancelled(false);
					} else {
						$event->setCancelled(true);
					}
				}
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					$event->setCancelled(true);
				}
			}
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function onHunger(PlayerExhaustEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					$event->setCancelled(true);
				}
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function onDeath(PlayerDeathEvent $event){
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					TNTWars::getTeam()->removeToTeam($name, $event->getPlayer());
				}
			}
		}
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {

					$message = $event->getMessage();
					if ($message{0} === "/") {
						$event->setCancelled(true);
						$command = substr($message, 1);
						$args = explode(" ", $command);
						switch ($args[0]) {
							case 'tntwars':
								if ($args[1] === 'exit') {
									TNTWars::getArena()->quit($name, $event->getPlayer());
								} else {
									$event->getPlayer()->sendMessage("§cUse /tntwars exit to leave the game.");
								}
								break;
							case 'tw':
								$event->setCancelled(false);
								break;
							case 'spawn':
							case 'lobby':
							case 'hub':
							default:
								$event->getPlayer()->sendMessage("§cUse /tntwars exit to leave the game.");
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					TNTWars::getTeam()->removeToTeam($name, $event->getPlayer());
				}
			}
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					TNTWars::getArena()->quit($name, $event->getPlayer());
				}
			}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onPVP(EntityDamageEvent $event): void
	{
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getEntity()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					switch ($event->getCause()) {
						case EntityDamageEvent::CAUSE_CONTACT:
						case EntityDamageEvent::CAUSE_DROWNING:
						case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
						case EntityDamageEvent::CAUSE_FALL:
						case EntityDamageEvent::CAUSE_FIRE:
						case EntityDamageEvent::CAUSE_FIRE_TICK:
						case EntityDamageEvent::CAUSE_LAVA:
						case EntityDamageEvent::CAUSE_MAGIC:
						case EntityDamageEvent::CAUSE_STARVATION:
						case EntityDamageEvent::CAUSE_SUFFOCATION:
						case EntityDamageEvent::CAUSE_SUICIDE:
						case EntityDamageEvent::CAUSE_VOID:
							$event->setCancelled(true);
							break;
					}
				}
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onTransaction(InventoryTransactionEvent $event): void
	{
		$player = $event->getTransaction()->getSource();
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($player->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					$event->setCancelled(true);
				}
			}
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 */
	public function onChat(PlayerChatEvent $event): void
	{
		$player = $event->getPlayer();
		if (empty(TNTWars::getInstance()->getDataFolder())) {
			return;
		}
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($event->getPlayer()->getLevel()->getFolderName() === TNTWars::getArena()->getLevel($name)) {
					if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$player->getName()])) {
						$event->setCancelled(true);
						TNTWars::getArena()->broadcast($name, '§9' . $player->getName() . '§f: §7' . $event->getMessage(), Arena::BROADCAST_MESSAGE);
					}

					if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$player->getName()])) {
						$event->setCancelled(true);
						TNTWars::getArena()->broadcast($name, '§c' . $player->getName() . '§f: §7' . $event->getMessage(), Arena::BROADCAST_MESSAGE);
					}
				}
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$scan = scandir(TNTWars::getInstance()->getDataFolder());
		foreach ($scan as $files) {
			if ($files !== '..' && $files !== '.') {
				if (!is_file(TNTWars::getInstance()->getDataFolder() . $files)){
					continue;
				}
				$name = str_replace('.yml', '', $files);
				if ($player->getLevel()->getFolderName() == TNTWars::getArena()->getLevel($name)) {
					if ($player->getGamemode() === Player::SPECTATOR){
						$event->setCancelled();
						return;
					}
					if($event->getItem()->getId() == ItemIds::TNT) {
						$this->addToThrowedList($player);
						$nbt = new CompoundTag("", [
							"Pos" => new ListTag("Pos", [
								new DoubleTag("", $player->x),
								new DoubleTag("", $player->y + $player->getEyeHeight()),
								new DoubleTag("", $player->z)
							]),
							"Motion" => new ListTag("Motion", [
								new DoubleTag("", -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)),
								new DoubleTag("", -sin($player->pitch / 180 * M_PI)),
								new DoubleTag("", cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))
							]),
							"Rotation" => new ListTag("Rotation", [
								new FloatTag("", $player->yaw),
								new FloatTag("", $player->pitch)
							]),
						]);
						if (isset(TNTWars::getData()->getPlayersRedTeam($name)[$player->getName()])) {
							$nbt->setString("OwningTeam", "RED");
						}
						if (isset(TNTWars::getData()->getPlayersBlueTeam($name)[$player->getName()])) {
							$nbt->setString("OwningTeam", "BLUE");
						}
						if ($this->inThrowedList($player)) {

							if ($this->throwedTNT[$player->getName()] > 4) {
								if (TNTWars::getPluginUtils()->checkCooldown($player, 5) === false) {
									$this->throwedTNT[$player->getName()] = 1;
								} else {
									$event->setCancelled();
									$player->sendMessage('§c[TNTWars] Please wait 5 sec before throw tnt');
									return;
								}
								return;
							}
							$tnt = Entity::createEntity("PrimedTNT", $player->getLevel(), $nbt, true);
							$tnt->setMotion($tnt->getMotion()->multiply(2));
							$tnt->setOwningEntity($player);
							$tnt->spawnToAll();
							$this->throwedTNT[$player->getName()]++;
						}
					}
				}
			}
		}
	}

}