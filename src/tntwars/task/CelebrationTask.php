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

class CelebrationTask extends Task
{
	/** @var string */
	private $arena;
	/** @var int */
	private $time = 10;

	/**
	 * CelebrationTask constructor.
	 * @param string $arena
	 */
	public function __construct(string $arena)
	{
		$this->arena = $arena;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick)
	{
		if (TNTWars::getArena()->getPlaying($this->arena) >= 1) {
			if ($this->time > 0) {
				$this->time--;
				TNTWars::getArena()->broadcast($this->arena, '§6Ending game in §a' . $this->time . ' §6second(s)', Arena::BROADCAST_BOSSBAR);
			}
			if ($this->time === 0) {
				foreach (TNTWars::getInstance()->getServer()->getLevelByName(TNTWars::getArena()->getLevel($this->arena))->getPlayers() as $player) {
					TNTWars::getArena()->quit($this->arena, $player);
				}
				TNTWars::getArena()->load($this->arena);
				TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
			}
		} else {
			foreach (TNTWars::getInstance()->getServer()->getLevelByName(TNTWars::getArena()->getLevel($this->arena))->getPlayers() as $player) {
				TNTWars::getArena()->quit($this->arena, $player);
			}
			TNTWars::getArena()->load($this->arena);
			TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}