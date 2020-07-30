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
namespace tntwars\entities;


use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use tntwars\TNTWars;

class GameEntity extends Human
{

	private const NAMETAG = "§f§lCLICK TO JOIN\n§eTNTWARS\n§aNEW UPDATES!";

	/** @var int */
	public $entityId;

	/**
	 * MainEntity constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 */
	public function __construct(Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
		$this->setNameTag(self::NAMETAG);
		$this->setNameTagAlwaysVisible(true);
		$this->setNameTagVisible(true);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return '';
	}

	/**
	 * @param int $currentTick
	 * @return bool
	 */
	public function onUpdate(int $currentTick): bool
	{
		if ($this->getScale() != 1.2) {
			$this->setScale(1.2);
		}
		$this->setNameTag(self::NAMETAG . "\n" . "§aPlaying: " . $this->getPlaying());
		return parent::onUpdate($currentTick);
	}

	/**
	 * @return int
	 */
	private function getPlaying()
	{
		return TNTWars::getArena()->getTotalPlaying();
	}
}