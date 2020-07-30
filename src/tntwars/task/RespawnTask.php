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

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use tntwars\utils\PluginUtils;
use tntwars\TNTWars;

class RespawnTask extends Task
{
	/** @var string */
	private $arena;
	/** @var Player */
	private $player;
	/** @var int */
	private $respawn = 6;

	/**
	 * RespawnTask constructor.
	 * @param string $arena
	 * @param Player $player
	 */
	public function __construct(string $arena, Player $player)
	{
		$this->arena = $arena;
		$this->player = $player;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick)
	{
		if ($this->player->getGamemode() === 3 && $this->respawn > 0) {
			switch ($this->respawn) {
				case 6:
					$this->player->sendTitle('§95', '§bRespawn in', 2);
					PluginUtils::playSound($this->player, 'random.pop2', 1, 1);
					break;
				case 5:
					$this->player->sendTitle('§94', '§bRespawn in', 2);
					PluginUtils::playSound($this->player, 'random.pop2', 1, 1);
					break;
				case 4:
					$this->player->sendTitle('§93', '§bRespawn in', 2);
					PluginUtils::playSound($this->player, 'random.pop2', 1, 1);
					break;
				case 3:
					$this->player->sendTitle('§92', '§bRespawn in', 2);
					PluginUtils::playSound($this->player, 'random.pop2', 1, 1);
					break;
				case 2:
					$this->player->sendTitle('§91', '§bRespawn in', 2);
					PluginUtils::playSound($this->player, 'random.pop2', 1, 1);
					break;
				case 1:
					if (isset(TNTWars::getData()->getPlayersBlueTeam($this->arena)[$this->player->getName()])) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $this->arena . '.yml', Config::YAML);
						$spawnBlue = explode(',', $arena->get('spawn_blue'));
						$this->player->teleport(new Vector3((float)$spawnBlue[0], (float)$spawnBlue[1], (float)$spawnBlue[2]));
						$this->player->setGamemode(0);
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 3, 3));
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 3, 2));
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 3, 2));
						$this->player->sendTitle(' ', ' ', 2);
					}

					if (isset(TNTWars::getData()->getPlayersRedTeam($this->arena)[$this->player->getName()])) {
						$arena = new Config(TNTWars::getInstance()->getDataFolder() . $this->arena . '.yml', Config::YAML);
						$spawnRed = explode(',', $arena->get('spawn_red'));
						$this->player->teleport(new Vector3((float)$spawnRed[0], (float)$spawnRed[1], (float)$spawnRed[2]));
						$this->player->setGamemode(0);
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 3, 3));
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 3, 2));
						$this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 3, 2));
						$this->player->sendTitle(' ', ' ', 2);
					}
					TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
					break;
			}
			if ($this->respawn > 0) {
				$this->respawn--;
			}
		} else {
			TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}