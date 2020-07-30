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

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use tntwars\TNTWars;

class PlayerCommand extends PluginCommand
{
	/**
	 * PlayerCommand constructor.
	 * @param TNTWars $plugin
	 */
	public function __construct(TNTWars $plugin)
	{
		parent::__construct("tntwars", $plugin);
		$this->setAliases(['tntwars']);
		$this->setDescription('TNTWars Player Command');
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return '/tntwars';
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
			if (isset($args[0])) {
				switch ($args[0]) {
					case 'join':
						$sender->sendMessage('§aLooking for an available TNTWars game...');
						if (!TNTWars::getArena()->join($sender)) {
							$sender->sendMessage('§cConnection error when trying to find an available server');
						}
						break;
					case 'exit':
						break;
				}
			} else {
				$sender->sendMessage('§7use: /tntwars [join|exit]');
			}
		} else {
			$sender->sendMessage('§cThe command can only be used by an Owner in play');
		}
		return true;
	}
}