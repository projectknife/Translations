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
	protected $uncomplete_lang = array("cs-CZ", "da-DK", "fa-IR", "fi-FI", "pt-PT", "sk-SK", "zh-TW", "ru-PO");
	
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
		$table = JTable::getInstance('extension');
		$id = $table->find(array('type'=>'component', 'element'=>'com_projectfork'));
		if(!$id) {
			$app->enqueueMessage(sprintf ( 'Projectfork %s has not been installed, aborting!', '4.x' ), 'notice');
			return false;
		}
		
		// TODO: Check installed version of PF4 and translations, give hints and tipps to do everything right!
		
		// Get list of languages to be installed. Only installs languages that are found in your system.
		$source = $parent->getParent()->getPath('source').'/languages';
		$languages = JFactory::getLanguage()->getKnownLanguages();
		
		$files = $parent->manifest->files;
		foreach ($languages as $language) {
			$search = JFolder::folders($source, $language['tag']); // no .zip files use "folders" instead
			if (empty($search)) continue;
			
			// Generate something like <file type="file" id="lang_pf4_en-GB">en-GB</file>
			$file = $files->addChild('file', array_pop($search));
			$file->addAttribute('type', 'file');
			$file->addAttribute('id', $this->name . '_' . $language['tag']);
			echo sprintf('<b>Installing detected language:</b> %s - %s ...', $language['tag'], $language['name']);
			if(in_array($language['tag'], $this->uncomplete_lang)) {
				echo ' <span style="color: darkorange;">(This language is not full translated at this moment. Please visit our <a href="https://github.com/projectfork/Translations/wiki" target="_blank">Projectfork Translations - Project Site</a> for more Informations and how to contribute!)';
			}
			echo '<br />';
		}
		
		if (empty($files)) {
			// No packages to install: replace failure message with something that's more descriptive.
			$app->enqueueMessage(sprintf ( 'Your site is English only. There\'s no need to install an other Projectfork Language! If you want to install a different language, you have to install a different core language first!' ), 'notice');
			return false;
		}
		
		return true;
	}
	
	public function uninstallLanguage($tag, $name) {
		$table = JTable::getInstance('extension');
		$id = $table->find(array('type'=>'file', 'element'=>"{$this->name}_{$tag}"));
		if(!$id) return;
		
		$installer = new JInstaller();
		$installer->uninstall ( 'file', $id );
	}
}