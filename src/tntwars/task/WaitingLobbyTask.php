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
namespace tntwars\task;

use pocketmine\scheduler\Task;
use tntwars\arena\Arena;
use tntwars\TNTWars;
use function scandir;
use function str_replace;

class WaitingLobbyTask extends Task
{
	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick)
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
				if (TNTWars::getArena()->getStatus($name) === Arena::STATUS_ENABLE && TNTWars::getArena()->getPlaying($name) < 2) {
					if (TNTWars::getData()->getTime($name) !== 30) {
						TNTWars::getData()->setTime($name, 30);
					}
					TNTWars::getArena()->broadcast($name, '§aLooking for more players...', Arena::BROADCAST_BOSSBAR);

				} else {
					if (TNTWars::getData()->getTime($name) > 0) {
						TNTWars::getArena()->broadcast($name, '§aTNTWars starts in §6' . TNTWars::getData()->getTime($name) . ' §asec.', Arena::BROADCAST_BOSSBAR);
						if (TNTWars::getData()->getTime($name) === 30 || TNTWars::getData()->getTime($name) === 20 || TNTWars::getData()->getTime($name) === 10 || TNTWars::getData()->getTime($name) < 6) {
							if (TNTWars::getData()->getTime($name) > 1) {
								TNTWars::getArena()->broadcast($name, 'note.xylophone', Arena::BROADCAST_SOUND);
							}
						}
					}

					if (TNTWars::getData()->getTime($name) === 5 && TNTWars::getData()->getTime($name) === 1) {
						foreach (TNTWars::getInstance()->getServer()->getLevelByName(TNTWars::getArena()->getLevel($name))->getPlayers() as $player) {
							if (TNTWars::getData()->isPlaying($name, $player)) {
								$player->getInventory()->clearAll();
								$player->setGamemode(0);
							}
						}
					}

					if (TNTWars::getData()->getTime($name) === 1) {
						TNTWars::getArena()->broadcast($name, 'random.levelup', Arena::BROADCAST_SOUND);
					}


					if (TNTWars::getData()->getTime($name) === 0) {
						TNTWars::getTeam()->teleportToSlot($name);
						TNTWars::getArena()->setStatus($name, Arena::STATUS_INGAME);
						TNTWars::getArena()->broadcast($name, '§6Map: §7' . $name, Arena::BROADCAST_MESSAGE);
						TNTWars::getArena()->broadcast($name, "§6FIGHT!", Arena::BROADCAST_TITLE);
						TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new GameTask($name), 15);
					}

					$ticks = TNTWars::getData()->getTime($name);
					$ticks--;
					TNTWars::getData()->setTime($name, $ticks);
				}

			}
		}
	}
}