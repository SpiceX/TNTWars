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
namespace tntwars\arena;

use pocketmine\level\Level;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use tntwars\TNTWars;
use ZipArchive;

/**
 * Class MapReset
 * @package tntwars\arena
 */
class MapReset
{

	/** @var TNTWars $plugin */
	public $plugin;

	/**
	 * MapReset constructor.
	 * @param TNTWars $plugin
	 */
	public function __construct(TNTWars $plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @param Level $level
	 */
	public function saveMap(Level $level)
	{
		$level->save(true);

		$levelPath = $this->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $level->getFolderName();
		$zipPath = $this->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $level->getFolderName() . ".zip";

		$zip = new ZipArchive();

		if (is_file($zipPath)) {
			unlink($zipPath);
		}

		$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($levelPath)), RecursiveIteratorIterator::LEAVES_ONLY);

		/** @var SplFileInfo $file */
		foreach ($files as $file) {
			if ($file->isFile()) {
				$filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
				$localPath = substr($filePath, strlen($this->plugin->getServer()->getDataPath() . "worlds"));
				$zip->addFile($filePath, $localPath);
			}
		}

		$zip->close();
	}

	/**
	 * @param string $folderName
	 * @param bool $justSave
	 *
	 * @return Level|null
	 */
	public function loadMap(string $folderName, bool $justSave = false): ?Level
	{
		if (!$this->plugin->getServer()->isLevelGenerated($folderName)) {
			return null;
		}

		if ($this->plugin->getServer()->isLevelLoaded($folderName)) {
			$this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelByName($folderName), true);
		}

		$zipPath = $this->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $folderName . ".zip";

		if (!file_exists($zipPath)) {
			return null;
		}

		$zipArchive = new ZipArchive();
		$zipArchive->open($zipPath);
		$zipArchive->extractTo($this->plugin->getServer()->getDataPath() . "worlds");
		$zipArchive->close();

		if ($justSave) {
			return null;
		}

		$this->plugin->getServer()->loadLevel($folderName);
		return $this->plugin->getServer()->getLevelByName($folderName);
	}
}