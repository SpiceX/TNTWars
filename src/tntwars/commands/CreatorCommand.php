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
namespace tntwars\commands;

use Exception;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use tntwars\arena\Arena;
use tntwars\entities\GameEntity;
use tntwars\TNTWars;
use function file_exists;

class CreatorCommand extends PluginCommand
{
	/** @var TNTWars */
	private $plugin;

	/**
	 * CreatorCommand constructor.
	 * @param TNTWars $plugin
	 */
	public function __construct(TNTWars $plugin)
	{
		parent::__construct("tw", $plugin);
		$this->setAliases(['tw']);
		$this->setDescription('TNTWars Admin command');
		$this->plugin = $plugin;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return '/tw';
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool|mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool
	{
		if ($sender instanceof Player) {
			if ($sender->isOp()) {
				if (isset($args[0]) && $args[0] === 'npc'){
					try {
						$nbt = Entity::createBaseNBT($sender->asVector3());
						$nbt->setTag(clone ($sender->namedtag->getCompoundTag('Skin')));
						$npc = new GameEntity($sender->getLevel(), $nbt);
						$npc->yaw = $sender->getYaw();
						$npc->pitch = $sender->getPitch();
						$npc->spawnToAll();
						$sender->sendMessage("§a[TNTWars] Game entity placed.");
					} catch (Exception $e){
						$sender->sendMessage("§c[TNTWars] There is a problem with your skin, please try again or change your aspect.");
					}
				} elseif (isset($args[0], $args[1]) && $args[0] === 'enable'){
					if (file_exists(TNTWars::getInstance()->getDataFolder() . $args[1] . '.yml')) {
						$level = Server::getInstance()->getLevelByName($args[1]);
						if ($level != null){
							TNTWars::getMapReset()->saveMap($level);
							$sender->sendMessage("§a[TNTWars] map {$args[1]} enabled.");
						} else {
							$sender->sendMessage('§cAn unexpected error happened, the arena does not exists or the world does not exist!');
						}
					} else {
						$sender->sendMessage('§cAn unexpected error happened, the arena does not exists or the world does not exist!');
					}
				} elseif (isset($args[0], $args[1]) && $args[0] === 'create') {
					if (!file_exists(TNTWars::getInstance()->getDataFolder() . $args[1] . '.yml') && file_exists(TNTWars::getInstance()->getServer()->getDataPath() . 'worlds/' . $args[1])) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $args[1] . '.yml', Config::YAML, [
							'level' => $args[1],
							'status' => Arena::STATUS_ENABLE,
							'spawn_blue' => null,
							'spawn_red' => null,
						]);
						$arena->save();
						TNTWars::getArena()->load($args[1]);
						$sender->sendMessage('§aThe arena has been created ' . $args[1]);
					} else {
						$sender->sendMessage('§cAn unexpected error happened, the arena already exists or the world does not exist!');
					}
				} elseif (isset($args[0], $args[1]) && $args[0] === 'spawn'){
					if (file_exists(TNTWars::getInstance()->getDataFolder() . $args[1] . '.yml')) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $args[1] . '.yml', Config::YAML);
						if ($args[2] === 'red' || $args[2] === 'blue'){
							$arena->set('spawn_' . $args[2], "{$sender->getX()},{$sender->getY()},{$sender->getZ()}");
							$arena->save();
							$sender->sendMessage("§aSpawn for team {$args[2]} configured!");
						} else
							$sender->sendMessage('§c/tw <spawn> <arena> <blue | red>');
					} else {
						$sender->sendMessage('§cAn unexpected error happened, the arena does not exists or the world does not exist!');
					}
				} else {
					$sender->sendMessage('§7use: /tw [create/spawn/enable]');
				}
			}
		} else {
			$sender->sendMessage('§cThe command can only be used by an owner in play');
		}
		return true;
	}
}