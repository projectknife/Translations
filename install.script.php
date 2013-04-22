<?php
/**
 * @package Projectfork
 * @subpackage Projectfork.Translations.Installer
 *
 * @copyright (C) 2006 - 2013 Projectfork Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.projectfork.net
 **/
defined( '_JEXEC' ) or die();

class pkg_projectfork_languagesInstallerScript {
	
	protected $name = 'lang_pf4';
	
	public function uninstall($parent) {
		$languages = JFactory::getLanguage()->getKnownLanguages();
		foreach ($languages as $language) {
			echo $this->uninstallLanguage($language['tag'], $language['name']);
		}
	}
	
	public function preflight($type, $parent) {
		if (!in_array($type, array('install', 'update'))) return true;
		
		$app = JFactory::getApplication();
		
		// Do not install if Projectfork 4 doesn't exist.
//		if (!defined('PF_FRAMEWORK')) {
//			$app->enqueueMessage(sprintf ( 'Projectfork %s has not been installed, aborting!', '4.x' ), 'notice');
//			return false;
//		}
		
			// Check the installed version of PF4 and the translation, give hints and tipps to do everything right!
		
		// Get list of languages to be installed. Only installs languages that are found in your system.
		$source = $parent->getParent()->getPath('source').'/language';
		$languages = JFactory::getLanguage()->getKnownLanguages();
		
		$files = $parent->manifest->files;
		foreach ($languages as $language) {
			$search = JFolder::folders($source, $language['tag']); // no .zip files use "folders" instead
			if (empty($search)) continue;
			
			// Generate something like <file type="file" client="site" id="com_kunena_fi-FI">com_kunena_fi-FI_v2.0.0.zip</file>
			$file = $files->addChild('file', array_pop($search));
			$file->addAttribute('type', 'file');
			$file->addAttribute('id', $this->name.'_'.$language['tag']);
			echo sprintf('Installing language %s - %s ...', $language['tag'], $language['name']) . '<br />';
		}
		
		if (empty($files)) {
			// No packages to install: replace failure message with something that's more descriptive.
			$app->enqueueMessage(sprintf ( 'Your site is English only. There\'s no need to install a Projectfork Language Pack!' ), 'notice');
			return false;
		}
		
		return true;
	}
	
	public function uninstallLanguage($tag, $name) {
		$table = JTable::getInstance('extension');
		$id = $table->find(array('type'=>'file', 'element'=>"{$this->name}_{$tag}"));
		if (!$id) return;
		
		$installer = new JInstaller();
		$installer->uninstall ( 'file', $id );
	}
}