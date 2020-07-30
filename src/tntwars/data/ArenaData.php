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
namespace tntwars\data;

use pocketmine\Player;
use tntwars\TNTWars;

class ArenaData
{

	/**
	 * @param string $arena
	 */
	public function add(string $arena): void
	{
		if (!isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena] = [
				'time_start' => 30,
				'blue_score' => 25,
				'red_score' => 25,
				'red_team' => [],
				'blue_team' => []
			];
		} else {
			$this->reset($arena);
		}
	}

	/**
	 * @param string $arena
	 */
	public function remove(string $arena): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			unset(TNTWars::$handle[$arena]);
		}
	}

	/**
	 * @param string $arena
	 */
	public function reset(string $arena): void
	{
		$this->remove($arena);
		$this->add($arena);
	}

	/**
	 * @param string $arena
	 * @return int
	 */
	public function getRedScore(string $arena): int
	{
		if (isset(TNTWars::$handle[$arena])) {
			return TNTWars::$handle[$arena]['red_score'];
		}
		return 0;
	}

	/**
	 * @param string $arena
	 * @return int
	 */
	public function getBlueScore(string $arena): int
	{
		if (isset(TNTWars::$handle[$arena])) {
			return TNTWars::$handle[$arena]['blue_score'];
		}
		return 0;
	}

	/**
	 * @param string $arena
	 * @param int $score
	 */
	public function setRedScore(string $arena, int $score): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena]['red_score'] = $score;
		}
	}

	/**
	 * @param string $arena
	 * @param int $score
	 */
	public function setBlueScore(string $arena, int $score): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena]['blue_score'] = $score;
		}
	}

	/**
	 * @param string $arena
	 * @return array
	 */
	public function getPlayersRedTeam(string $arena): array
	{
		if (isset(TNTWars::$handle[$arena])) {
			return TNTWars::$handle[$arena]['red_team'];
		}
		return [];
	}

	/**
	 * @param string $arena
	 * @return array
	 */
	public function getPlayersBlueTeam(string $arena): array
	{
		if (isset(TNTWars::$handle[$arena])) {
			return TNTWars::$handle[$arena]['blue_team'];
		}
		return [];
	}

	/**
	 * @param string $arena
	 * @param Player $player
	 */
	public function setPlayerRedTeam(string $arena, Player $player): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena]['red_team'][$player->getName()] = $player->getName();
		}
	}

	/**
	 * @param string $arena
	 * @param Player $player
	 */
	public function setPlayerBlueTeam(string $arena, Player $player): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena]['blue_team'][$player->getName()] = $player->getName();
		}
	}

	/**
	 * @param string $arena
	 * @return int
	 */
	public function getMaxPlayersRedTeam(string $arena): int
	{
		if (isset(TNTWars::$handle[$arena])) {
			return count(TNTWars::$handle[$arena]['red_team']);
		}
		return 0;
	}

	/**
	 * @param string $arena
	 * @return int
	 */
	public function getMaxPlayersBlueTeam(string $arena): int
	{
		if (isset(TNTWars::$handle[$arena])) {
			return count(TNTWars::$handle[$arena]['blue_team']);
		}
		return 0;
	}

	/**
	 * @param string $arena
	 * @param Player $player
	 */
	public function removePlayerRedTeam(string $arena, Player $player): void
	{
		if (isset(TNTWars::$handle[$arena], TNTWars::$handle[$arena]['red_team'][$player->getName()])) {
			unset(TNTWars::$handle[$arena]['red_team'][$player->getName()]);
		}
	}

	/**
	 * @param string $arena
	 * @param Player $player
	 */
	public function removePlayerBlueTeam(string $arena, Player $player): void
	{
		if (isset(TNTWars::$handle[$arena], TNTWars::$handle[$arena]['blue_team'][$player->getName()])) {
			unset(TNTWars::$handle[$arena]['blue_team'][$player->getName()]);
		}
	}

	/**
	 * @param string $arena
	 * @param Player $player
	 * @return bool
	 */
	public function isPlaying(string $arena, Player $player): bool
	{
		if (isset($this->getPlayersBlueTeam($arena)[$player->getName()])) {
			return true;
		}
		if (isset($this->getPlayersRedTeam($arena)[$player->getName()])) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $arena
	 * @return int
	 */
	public function getTime(string $arena): int
	{
		if (isset(TNTWars::$handle[$arena])) {
			return TNTWars::$handle[$arena]['time_start'];
		}
		return 30;
	}

	/**
	 * @param string $arena
	 * @param int $time
	 */
	public function setTime(string $arena, int $time): void
	{
		if (isset(TNTWars::$handle[$arena])) {
			TNTWars::$handle[$arena]['time_start'] = $time;
		}
	}

}