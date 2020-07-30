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
use function floor;

class GameTask extends Task
{
	/** @var string */
	private $arena;
	/** @var null */
	private $action = null;
	/** @var int */
	private $time = 110;
	/** @var int */
	private $timeout = 0;

	/**
	 * GameTask constructor.
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
		if (TNTWars::getArena()->getPlaying($this->arena) > 0 && TNTWars::getData()->getMaxPlayersBlueTeam($this->arena) > 0 && TNTWars::getData()->getMaxPlayersRedTeam($this->arena) > 0) {
			$score = '§l§9B§r§7: §6' . TNTWars::getData()->getBlueScore($this->arena) . '§7 - [§b' . $this->getTime($this->time) . '§7] - §l§cR§r§7: §6' . TNTWars::getData()->getRedScore($this->arena);
			TNTWars::getArena()->broadcast($this->arena, $score, Arena::BROADCAST_BOSSBAR);
			TNTWars::getArena()->broadcast($this->arena, '', Arena::BROADCAST_SCOREBOARD);

			if ($this->action === null && $this->time === 110) {
				$this->action = 'building';
				$this->time = 30;
				TNTWars::getArena()->sendMode($this->arena, Arena::MODE_BUILDER);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, '§eYou have 30 seconds to build!', Arena::BROADCAST_MESSAGE);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, 'block.bell.hit', Arena::BROADCAST_SOUND);
			}

			if ($this->action === 'building' && $this->time === 0) {
				$this->action = 'battle';
				$this->time = 110;
				TNTWars::getArena()->sendMode($this->arena, Arena::MODE_BATTLE);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, '§eConstruction time is over, its time to fight!', Arena::BROADCAST_MESSAGE);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, 'block.bell.hit', Arena::BROADCAST_SOUND);
			}

			if ($this->action === 'battle' && $this->time === 0) {
				$this->action = 'building';
				$this->time = 30;
				TNTWars::getArena()->sendMode($this->arena, Arena::MODE_BUILDER);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, '§eYou have 30 seconds to build!', Arena::BROADCAST_MESSAGE);
				TNTWars::getInstance()->getArena()->broadcast($this->arena, 'block.bell.hit', Arena::BROADCAST_SOUND);
			}

			if ($this->time > 0)
			{
				$this->time--;
			}

			$this->timeout++;

			TNTWars::getArena()->getPossibleMethodsOfWinning($this->arena, $this->getTaskId(), $this->timeout);

		} else {
			TNTWars::getArena()->getPossibleMethodsOfWinning($this->arena, $this->getTaskId(), $this->timeout);
		}
	}

	/**
	 * @param int $int
	 * @return string
	 */
	public function getTime(int $int): string
	{
		$m = floor($int / 60);
		$s = floor($int % 60);
		return (($m < 10 ? '0' : '') . $m . '§7:§b' . ($s < 10 ? '0' : '') . $s);
	}
}