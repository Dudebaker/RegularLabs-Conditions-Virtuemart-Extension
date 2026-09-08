<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	namespace Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper;
	
	use RuntimeException;
	
	defined('_JEXEC') or die;
	
	/**
	 * Injects hooks into core files on install/update (hash-checked) or force.
	 *
	 * @since version
	 */
	class CoreFileExtenderHelper
	{
		private static bool $forceApply = false;
		
		private static mixed $currentInstaller = null;
		
		/**
		 * @var array<string, string>
		 */
		private static array $fileHashCache = [];
		
		/**
		 * Force-apply all patches (plugin force param / own install).
		 *
		 * @since version
		 */
		public static function ensureOverrides() : void
		{
			self::checkOverrides(null, true);
		}
		
		/**
		 * Run extenders for an installer event (or forced).
		 *
		 * @param mixed $installer
		 * @param bool  $force
		 *
		 * @since version
		 */
		public static function checkOverrides(mixed $installer = null, bool $force = false) : void
		{
			self::$forceApply       = $force;
			self::$currentInstaller = $installer;
			self::$fileHashCache    = [];
			
			try
			{
				$directory = __DIR__ . DIRECTORY_SEPARATOR . 'CoreFileExtender';
				$files     = @scandir($directory);
				
				if ($files === false)
				{
					return;
				}
				
				foreach ($files as $file)
				{
					if (!str_ends_with($file, '.php'))
					{
						continue;
					}
					
					require_once $directory . DIRECTORY_SEPARATOR . $file;
					
					$functionName = substr($file, 0, -4);
					
					if (function_exists($functionName))
					{
						$functionName();
					}
				}
			} finally
			{
				self::$forceApply       = false;
				self::$currentInstaller = null;
			}
		}
		
		/**
		 * @param mixed $installer
		 * @param array $extensionName
		 *
		 * @return bool
		 * @since version
		 */
		public static function checkInstaller(mixed $installer, array $extensionName) : bool
		{
			if (!is_object($installer) || !property_exists($installer, 'manifest') || $installer->manifest === null)
			{
				return false;
			}
			
			$installerExtensionNames   = [];
			$installerExtensionNames[] = (string)$installer->manifest->name;
			
			foreach (['files', 'files_j3', 'files_j4', 'files_j5'] as $additionalFile)
			{
				if (!property_exists($installer->manifest, $additionalFile))
				{
					continue;
				}
				
				foreach ($installer->manifest->$additionalFile->file as $file)
				{
					$installerExtensionNames[] = (string)$file;
				}
			}
			
			return !empty(array_intersect(
				array_map('strtolower', $installerExtensionNames),
				array_map('strtolower', $extensionName)
			));
		}
		
		/**
		 * Inject custom code before/after an anchor line in a core file.
		 *
		 * @param array       $extensionNames
		 * @param string      $extendName
		 * @param array       $extendContent
		 * @param string      $extendFile Relative to JPATH_ROOT
		 * @param string|null $extendBefore
		 * @param string|null $extendAfter
		 * @param float       $extendVersion
		 *
		 * @since version
		 */
		public static function handleCoreFileExtender(array   $extensionNames, string $extendName, array $extendContent, string $extendFile,
		                                              ?string $extendBefore = null, ?string $extendAfter = null, float $extendVersion = 1) : void
		{
			if (empty($extendBefore) && empty($extendAfter))
			{
				return;
			}
			
			if (!self::$forceApply && !self::checkInstaller(self::$currentInstaller, $extensionNames))
			{
				return;
			}
			
			$extendFile = JPATH_ROOT . DIRECTORY_SEPARATOR . $extendFile;
			
			if (!file_exists($extendFile))
			{
				return;
			}
			
			$extenderPrefix = 'Core File Extender';
			
			if (str_ends_with(strtolower($extendFile), '.xml'))
			{
				$extendNameVersion        = "<!-- $extenderPrefix - $extendName # v$extendVersion -->";
				$extendNameWithoutVersion = "<!-- $extenderPrefix - $extendName -->";
				$extendContentEnd         = "<!-- END $extenderPrefix -->";
			} else
			{
				$extendNameVersion        = "### $extenderPrefix - $extendName # v$extendVersion ###";
				$extendNameWithoutVersion = "### $extenderPrefix - $extendName #";
				$extendContentEnd         = "### END $extenderPrefix ###";
			}
			
			if (self::patchStateMatches($extendFile, $extendNameVersion))
			{
				return;
			}
			
			$fileContent = file_get_contents($extendFile);
			
			if ($fileContent === false)
			{
				return;
			}
			
			if (str_contains($fileContent, $extendNameVersion))
			{
				self::storePatchState($extendFile, $extendNameVersion);
				
				return;
			}
			
			$fileLineSeparator = self::detectNewlineType($fileContent);
			$extendPadding     = self::getCoreFileExtensionPadding($fileContent, $fileLineSeparator, $extendBefore, $extendAfter);
			
			if (str_contains($fileContent, $extendPadding . $extendNameWithoutVersion))
			{
				$fileContent = self::removeCoreFileExtension(
					$fileContent,
					$fileLineSeparator,
					$extendPadding . $extendNameWithoutVersion,
					$extendContentEnd,
					!empty($extendAfter)
				);
			}
			
			self::addCoreFileExtension(
				$extendFile,
				$fileContent,
				$fileLineSeparator,
				$extendNameVersion,
				$extendContent,
				$extendContentEnd,
				$extendPadding,
				$extendBefore,
				$extendAfter
			);
			self::storePatchState($extendFile, $extendNameVersion);
		}
		
		/**
		 * Copy plugin-shipped files into a core component when missing or outdated.
		 * Skips entirely when the last sync fingerprint still matches (no per-request copy walk).
		 *
		 * @param array  $extensionNames
		 * @param string $source
		 * @param string $destination
		 *
		 * @since version
		 */
		public static function handleFileCopy(array $extensionNames, string $source, string $destination) : void
		{
			if (!self::$forceApply && !self::checkInstaller(self::$currentInstaller, $extensionNames))
			{
				return;
			}
			
			if ($source === '' || $destination === '' || !is_dir($source))
			{
				return;
			}
			
			$fingerprint = self::getFileCopyFingerprint($source, $destination);
			$statePath   = self::getFileCopyStatePath($source, $destination);
			
			if (is_file($statePath) && hash_equals($fingerprint, trim((string)file_get_contents($statePath))))
			{
				return;
			}
			
			self::copyFilesRecursive($source, $destination);
			
			$hashDir = self::getHashDirectory();
			
			if (!is_dir($hashDir) && !mkdir($hashDir, 0755, true) && !is_dir($hashDir))
			{
				return;
			}
			
			// Recompute after copy so dest stats are current
			file_put_contents($statePath, self::getFileCopyFingerprint($source, $destination));
		}
		
		/**
		 * Fingerprint of a source tree + mirrored destination presence/size/mtime.
		 * Uses stat only (no file content reads) for the per-request check.
		 *
		 * @param string $source
		 * @param string $destination
		 *
		 * @return string
		 * @since version
		 */
		private static function getFileCopyFingerprint(string $source, string $destination) : string
		{
			$entries = [];
			self::collectFileCopyFingerprint($source, $destination, '', $entries);
			ksort($entries);
			
			return sha1(serialize($entries));
		}
		
		/**
		 * @param string               $sourceRoot
		 * @param string               $destinationRoot
		 * @param string               $relative
		 * @param array<string,string> $entries
		 *
		 * @since version
		 */
		private static function collectFileCopyFingerprint(string $sourceRoot, string $destinationRoot, string $relative, array &$entries) : void
		{
			$path = $relative === '' ? $sourceRoot : $sourceRoot . '/' . $relative;
			$dir  = @opendir($path);
			
			if ($dir === false)
			{
				return;
			}
			
			while (($file = readdir($dir)) !== false)
			{
				if ($file === '.' || $file === '..')
				{
					continue;
				}
				
				$rel        = $relative === '' ? $file : $relative . '/' . $file;
				$sourcePath = $sourceRoot . '/' . $rel;
				$destPath   = $destinationRoot . '/' . $rel;
				
				if (is_dir($sourcePath))
				{
					self::collectFileCopyFingerprint($sourceRoot, $destinationRoot, $rel, $entries);
					
					continue;
				}
				
				if (!is_file($sourcePath))
				{
					continue;
				}
				
				$sourceStat = @stat($sourcePath);
				$destStat   = is_file($destPath) ? @stat($destPath) : false;
				
				$entries[$rel] = implode(
					'|',
					[
						(string)($sourceStat['size'] ?? 0),
						(string)($sourceStat['mtime'] ?? 0),
						$destStat === false ? 'missing' : (string)$destStat['size'],
						$destStat === false ? '0' : (string)$destStat['mtime'],
					]
				);
			}
			
			closedir($dir);
		}
		
		/**
		 * @param string $source
		 * @param string $destination
		 *
		 * @return string
		 * @since version
		 */
		private static function getFileCopyStatePath(string $source, string $destination) : string
		{
			return self::getHashDirectory() . DIRECTORY_SEPARATOR . sha1($source . "\0" . $destination) . '.copy.hash';
		}
		
		/**
		 * @param string $source
		 * @param string $destination
		 *
		 * @since version
		 */
		private static function copyFilesRecursive(string $source, string $destination) : void
		{
			if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination))
			{
				throw new RuntimeException(sprintf('Directory "%s" was not created', $destination));
			}
			
			$dir = opendir($source);
			
			if ($dir === false)
			{
				return;
			}
			
			while (($file = readdir($dir)) !== false)
			{
				if ($file === '.' || $file === '..')
				{
					continue;
				}
				
				$sourcePath = $source . '/' . $file;
				$destPath   = $destination . '/' . $file;
				
				if (is_dir($sourcePath))
				{
					self::copyFilesRecursive($sourcePath, $destPath);
					
					continue;
				}
				
				if (!is_file($sourcePath))
				{
					continue;
				}
				
				$sourceHash = (string)sha1_file($sourcePath);
				
				if (is_file($destPath) && hash_equals($sourceHash, (string)sha1_file($destPath)))
				{
					continue;
				}
				
				if (!copy($sourcePath, $destPath))
				{
					closedir($dir);
					
					throw new RuntimeException(sprintf('Failed to copy "%s"', $sourcePath));
				}
				
				chmod($destPath, 0644);
				self::invalidateOpcache($destPath);
			}
			
			closedir($dir);
		}
		
		/**
		 * @param string $extendFile Absolute path
		 * @param string $extendNameVersion
		 *
		 * @return bool
		 * @since version
		 */
		private static function patchStateMatches(string $extendFile, string $extendNameVersion) : bool
		{
			$stateFile = self::getPatchStatePath($extendFile, $extendNameVersion);
			
			if (!is_file($stateFile))
			{
				return false;
			}
			
			$stored = trim((string)file_get_contents($stateFile));
			
			if ($stored === '')
			{
				return false;
			}
			
			return hash_equals($stored, self::getCoreFileHash($extendFile));
		}
		
		/**
		 * @param string $extendFile Absolute path
		 * @param string $extendNameVersion
		 *
		 * @since version
		 */
		private static function storePatchState(string $extendFile, string $extendNameVersion) : void
		{
			$hashDir = self::getHashDirectory();
			
			if (!is_dir($hashDir) && !mkdir($hashDir, 0755, true) && !is_dir($hashDir))
			{
				return;
			}
			
			unset(self::$fileHashCache[$extendFile]);
			$hash = self::getCoreFileHash($extendFile);
			file_put_contents(self::getPatchStatePath($extendFile, $extendNameVersion), $hash);
		}
		
		/**
		 * @param string $extendFile Absolute path
		 *
		 * @return string
		 * @since version
		 */
		private static function getCoreFileHash(string $extendFile) : string
		{
			if (!isset(self::$fileHashCache[$extendFile]))
			{
				self::$fileHashCache[$extendFile] = (string)sha1_file($extendFile);
			}
			
			return self::$fileHashCache[$extendFile];
		}
		
		/**
		 * @param string $extendFile Absolute path
		 * @param string $extendNameVersion
		 *
		 * @return string
		 * @since version
		 */
		private static function getPatchStatePath(string $extendFile, string $extendNameVersion) : string
		{
			return self::getHashDirectory() . DIRECTORY_SEPARATOR . sha1($extendFile . "\0" . $extendNameVersion) . '.hash';
		}
		
		/**
		 * @return string
		 * @since version
		 */
		private static function getHashDirectory() : string
		{
			return __DIR__ . DIRECTORY_SEPARATOR . 'CoreFileExtender' . DIRECTORY_SEPARATOR . 'hashes';
		}
		
		/**
		 * @param string $file Absolute path
		 *
		 * @since version
		 */
		private static function invalidateOpcache(string $file) : void
		{
			if (function_exists('opcache_invalidate'))
			{
				@opcache_invalidate($file, true);
			}
		}
		
		/**
		 * @param string $content
		 *
		 * @return string
		 * @since version
		 */
		private static function detectNewlineType(string $content) : string
		{
			$arr = array_count_values(
				explode(
					' ',
					preg_replace(
						'/[^\r\n]*(\r\n|\n|\r)/',
						'\1 ',
						$content
					)
				)
			);
			
			arsort($arr);
			
			$newLineType = key($arr);
			
			if (is_null($newLineType) || is_numeric($newLineType))
			{
				return "\n";
			}
			
			return (string)$newLineType;
		}
		
		/**
		 * @param string      $fileContent
		 * @param string      $fileLineSeparator
		 * @param string|null $extendBefore
		 * @param string|null $extendAfter
		 *
		 * @return string
		 * @since version
		 */
		private static function getCoreFileExtensionPadding(string $fileContent, string $fileLineSeparator, ?string $extendBefore = null, ?string $extendAfter = null) : string
		{
			$textTillExtendBeforeAfter = substr($fileContent, 0, strpos($fileContent, $extendBefore ?? $extendAfter));
			
			return substr($textTillExtendBeforeAfter, strrpos($textTillExtendBeforeAfter, $fileLineSeparator) + strlen($fileLineSeparator));
		}
		
		/**
		 * @param string      $extendFile
		 * @param string      $fileContent
		 * @param string      $fileLineSeparator
		 * @param string      $extendNameVersion
		 * @param array       $extendContent
		 * @param string      $extendContentEnd
		 * @param string      $extendPadding
		 * @param string|null $extendBefore
		 * @param string|null $extendAfter
		 *
		 * @since version
		 */
		private static function addCoreFileExtension(string $extendFile, string $fileContent, string $fileLineSeparator, string $extendNameVersion,
		                                             array  $extendContent, string $extendContentEnd, string $extendPadding, ?string $extendBefore = null, ?string $extendAfter = null) : void
		{
			$extendText = '';
			
			if (!is_null($extendAfter))
			{
				$extendText .= $extendPadding . $extendAfter . $fileLineSeparator . $fileLineSeparator;
			}
			
			$extendText .= $extendPadding . $extendNameVersion . $fileLineSeparator;
			
			foreach ($extendContent as $ec)
			{
				$extendText .= $extendPadding . $ec . $fileLineSeparator;
			}
			
			$extendText .= $extendPadding . $extendContentEnd . $fileLineSeparator;
			
			if (!is_null($extendBefore))
			{
				$extendText .= $fileLineSeparator . $extendPadding . $extendBefore;
			}
			
			file_put_contents($extendFile, str_replace($extendPadding . ($extendBefore ?? $extendAfter), $extendText, $fileContent));
			unset(self::$fileHashCache[$extendFile]);
			self::invalidateOpcache($extendFile);
		}
		
		/**
		 * @param string $fileContent
		 * @param string $fileLineSeparator
		 * @param string $extendName
		 * @param string $extendContentEnd
		 * @param bool   $extendAfter
		 *
		 * @return string
		 * @since version
		 */
		private static function removeCoreFileExtension(string $fileContent, string $fileLineSeparator, string $extendName, string $extendContentEnd, bool $extendAfter = false) : string
		{
			$removeLineSeparatorsBefore = strlen($fileLineSeparator);
			
			if ($extendAfter)
			{
				$removeLineSeparatorsBefore += strlen($fileLineSeparator);
			}
			
			$oldVersionPos    = strpos($fileContent, $extendName) - $removeLineSeparatorsBefore;
			$oldVersionPosEnd = strpos($fileContent, $extendContentEnd) + strlen($extendContentEnd) + strlen($fileLineSeparator);
			$oldVersionText   = substr($fileContent, $oldVersionPos, $oldVersionPosEnd - $oldVersionPos);
			
			return str_replace($oldVersionText, '', $fileContent);
		}
	}
