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
namespace tntwars\utils;

use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Level;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class CustomExplosion extends Explosion
{

	protected $what;

	/**
	 * @return bool
	 */
	public function explodeB(): bool
	{
		$send = [];
		$updateBlocks = [];
		$source = $this->source->asVector3()->floor();
		$yield = (1 / $this->size) * 100;
		if ($this->what instanceof Entity) {
			$ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield);
			$ev->setBlockList([]);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			} else {
				$this->affectedBlocks = $ev->getBlockList();
			}
		}

		foreach ($this->affectedBlocks as $block) {
			if ($block instanceof TNT) {
				$block->ignite(mt_rand(10, 30));
			}
			$this->level->setBlockIdAt($block->x, $block->y, $block->z, Block::AIR);
			$pos = new Vector3($block->x, $block->y, $block->z);
			for ($side = 0; $side <= 5; $side++) {
				$sideBlock = $pos->getSide($side);
				if (!$this->level->isInWorld($sideBlock->x, $sideBlock->y, $sideBlock->z)) {
					continue;
				}
				if (!isset($this->affectedBlocks[$index = Level::blockHash($sideBlock->x, $sideBlock->y, $sideBlock->z)]) and !isset($updateBlocks[$index])) {
					$ev = new BlockUpdateEvent($this->level->getBlock($sideBlock));
					$ev->call();
					if (!$ev->isCancelled()) {
						$ev->getBlock()->onScheduledUpdate();
					}
					$updateBlocks[$index] = true;
				}
			}
			$send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
		}
		$this->level->addParticle(new HugeExplodeSeedParticle($source));
		$this->level->broadcastLevelSoundEvent($source, LevelSoundEventPacket::SOUND_EXPLODE);
		return true;
	}
}