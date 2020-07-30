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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use tntwars\utils\Fireworks;
use tntwars\utils\FireworksRocket;
use tntwars\utils\PluginUtils;
use tntwars\TNTWars;

class DefaultAnimation extends Task
{
	/** @var Player */
	private $player;
	/** @var string */
	private $level;
	/** @var int */
	private $time = 8;

	/**
	 * DefaultAnimation constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
		$this->level = $player->getLevel()->getFolderName();
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick)
	{
		if ($this->player->getLevel() !== null && $this->player !== null) {
			if ($this->player->getLevel()->getFolderName() === $this->level) {
				if ($this->time === 8) {
					PluginUtils::playSound($this->player, 'random.levelup', 1, 1);
					$this->player->getInventory()->clearAll();
					$this->player->getArmorInventory()->clearAll();
				}
				if ($this->time > 0) {
					$this->time--;
					/** @var Fireworks $fw */
					$fw = ItemFactory::get(Item::FIREWORKS);
					$fw->addExplosion($fw->getRandomType(), $fw->getRandomColor(), '', false, false);
					$fw->setFlightDuration(2);

					$level = $this->player->getLevel();
					$vector3 = $this->player->asVector3()->add(0.5, 1, 0.5);
					$nbt = FireworksRocket::createBaseNBT($vector3, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
					$entity = FireworksRocket::createEntity('FireworksRocket', $level, $nbt, $fw);
					if ($entity instanceof FireworksRocket) {
						$entity->spawnToAll();
					}
				}
				if ($this->time === 0) {
					TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
				}
			} else {
				TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
			}
		} else {
			TNTWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}