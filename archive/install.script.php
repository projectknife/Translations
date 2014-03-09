<?php
/**
 * @package Projectfork
 * @subpackage Projectfork.Translations.Installer
 *
 * @copyright (C) 2012 - 2014 Projectfork Translation Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.projectfork.net
 **/
defined( '_JEXEC' ) or die();

class pkg_projectfork_languagesInstallerScript {
	
	protected $name = 'lang_pf4';
	protected $uncomplete_lang = array(
		// "ar-AA", // Last Update: 2014-02-16 - 100%
		// "nl-NL", // Last Update: 2014-01-25 - 100%
		// "fr-FR", // Last Update: 2014-01-18 - 100%
		// "de-DE", // Last Update: 2014-01-15 - 100%
		// "hu-HU", // Last Update: 2014-02-13 - 100%
		// "pt-BR", // Last Update: 2014-03-07 - 100%
		// "ru-RU", // Last Update: 2014-02-24 - 100%
		// "tr-TR", // Last Update: 2014-02-24 - 100%
		"eu-ES", // Last Update: 2014-01-28 - 99%
		"cs-CZ", // Last Update: 2014-02-05 - 97%
		"da-DK", // Last Update: 2014-01-14 - 95%
		"es-ES", // Last Update: 2014-01-14 - 95%
		"nb-NO", // Last Update: 2014-01-14 - 93%
		"pl-PL", // Last Update: 2014-01-14 - 93%
		"th-TH", // Last Update: 2014-01-14 - 93%
		"ca-ES", // Last Update: 2014-01-14 - 92%
		"zh-TW", // Last Update: 2014-01-14 - 92%
		"it-IT", // Last Update: 2014-01-14 - 92%
		"ja-JP", // Last Update: 2014-01-14 - 92%
		"ro-RO", // Last Update: 2014-01-14 - 92%
		"pt-PT", // Last Update: 2014-01-14 - 90%
		"el-GR", // Last Update: 2014-01-14 - 89%
		"sv-SE", // Last Update: 2014-01-14 - 87%
		"sk-SK", // Last Update: 2014-01-14 - 66%
		"uk-UA", // Last Update: 2014-01-14 - 55%
		"nl-BE", // Last Update: 2014-01-14 - 39%
		"bg-BG", // Last Update: 2014-01-14 - 33%
		"fa-IR", // Last Update: 2014-01-23 - 33%
		"es-MX"  // Last Update: 2014-01-14 - 29%
	);
	
	public function uninstall($parent) {
		$languages = JFactory::getLanguage()->getKnownLanguages();
		foreach ($languages as $language) {
			echo $this->uninstallLanguage($language['tag'], $language['name']);
		}
	}
	
	public function preflight($type, $parent) {
		$lang = JFactory::getLanguage();
		$lang->load('pkg_projectfork_languages', dirname(__FILE__) . '/installer', 'en-GB', true);
		$lang->load('pkg_projectfork_languages', dirname(__FILE__) . '/installer', $lang->getTag(), true);
		
		if (!in_array($type, array('install', 'update'))) return true;
		
		$app = JFactory::getApplication();
		
		// Do not install if Projectfork 4 doesn't exist.
		$table = JTable::getInstance('extension');
		$id = $table->find(array('type'=>'component', 'element'=>'com_projectfork'));
		if(!$id) {
			$app->enqueueMessage(sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_PFNOTINSTALLED'), '4.x'), 'error');
			return false;
		}
		
		// Get list of languages to be installed. Only installs languages that are found in your system.
		$source = $parent->getParent()->getPath('source').'/languages';
		$languages = JFactory::getLanguage()->getKnownLanguages();
		
		$files = $parent->manifest->files;
		$installed_langs_html = '<ul>';
		
		foreach ($languages as $language) {
			$search = JFolder::folders($source, $language['tag']); // no .zip files use "folders" instead
			if (empty($search)) continue;
			
			// Generate something like <file type="file" id="lang_pf4_en-GB">en-GB</file>
			$file = $files->addChild('file', array_pop($search));
			$file->addAttribute('type', 'file');
			$file->addAttribute('id', $this->name . '_' . $language['tag']);
			$installed_langs_html .= '<li>' . sprintf('<b>%s</b> - %s', $language['tag'], $language['name']);
			if(in_array($language['tag'], $this->uncomplete_lang)) {
				$installed_langs_html .= ' ... <span style="color: darkorange;">(' . sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_NOTFULL_TRANSLATED'), '<a href="https://github.com/projectfork/Translations/wiki" target="_blank">Projectfork Translations Team</a>') . ')';
			}
			$installed_langs_html .= '</li>';
		}
		$installed_langs_html .= '</ul>';
		
		if (empty($files)) {
			// No packages to install
			$app->enqueueMessage(sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_ENGLISH_ONLY')), 'notice');
			return false;
		} else {
			// Package was installation successfull with detected languages
			echo JText::_('PKG_PROJECTFORK_LANGUAGES_INSTALLED_DETECTED_SUCCESS') . $installed_langs_html;
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