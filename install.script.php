<?php
/**
 * @package Projectfork
 * @subpackage Projectfork.Translations.Installer
 *
 * @copyright (C) 2006 - 2013 Projectfork Translation Team. All rights reserved.
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
		$lang = JFactory::getLanguage();
		$lang->load('pkg_projectfork_languages', dirname(__FILE__) . '/installer', 'en-GB', true);
		$lang->load('pkg_projectfork_languages', dirname(__FILE__) . '/installer', $lang->getTag(), true);
		
		if (!in_array($type, array('install', 'update'))) return true;
		
		$app = JFactory::getApplication();
		
		// Do not install if Projectfork 4 doesn't exist.
		$table = JTable::getInstance('extension');
		$id = $table->find(array('type'=>'component', 'element'=>'com_projectfork'));
		if($id) {
			$app->enqueueMessage(sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_PFNOTINSTALLED'), '4.x'), 'error');
			
			$nopf_installed = '
				<div style="display: inline-block;">
					<div style="background: url(https://projectfork.net/templates/hydra/img/logo.png) no-repeat 50% 50% #698C00; height: 63px; width: 205px; border: 6px solid #567300; padding: 10px; border-top-left-radius: 5px; border-bottom-left-radius: 5px; float: left;"></div>
					<div onclick="PFInstaller()" style="background-color: #698C00; cursor: pointer; height: 63px; border-width: 6px; border-style: solid; border-color: #567300; border-left: 0px; padding: 10px; color: white; font-size: 25px; line-height: 60px; float: left; text-indent: 0; text-align: center;">' . JText::_('PKG_PROJECTFORK_LANGUAGES_INSTALLNOW') . '</div>
					<div onclick="PFDownload()" style="background-color: #42A9CA; cursor: pointer; height: 63px; border: 6px solid #2E88A5; padding: 10px; color: white; font-size: 25px; line-height: 60px; float: left; text-indent: 0; text-align: center;">' . JText::_('PKG_PROJECTFORK_LANGUAGES_DOWNLOAD_COMMUNITY') . '</div>
					<div onclick="PFOrderPro()" style="background-color: #F7A700; cursor: pointer; height: 63px; border: 6px solid #BE8100; padding: 10px; color: white; font-size: 25px; line-height: 60px; float: left; text-indent: 0; text-align: center; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">' . JText::_('PKG_PROJECTFORK_LANGUAGES_ORDER_PRO') . '</div>
				</div>
				<script type="text/javascript">
					PFInstaller = function(pressbutton) {
						var form = document.getElementById("adminForm");
						form.install_url.value = "https://projectfork.net/downloads/projectfork-4/projectfork-4-4-0-0/pkgprojectfork4-0-0-zip?format=raw";
						Joomla.submitbutton4();
					}					
					PFDownload = function(pressbutton) {
						window.open("https://projectfork.net","_blank");
					}					
					PFOrderPro = function(pressbutton) {
						window.open("https://projectfork.net/pro","_blank");
					}					
				</script>
			';
			$app->enqueueMessage($nopf_installed, 'notice');
			return false;
		}
		
		// Get list of languages to be installed. Only installs languages that are found in your system.
		$source = $parent->getParent()->getPath('source').'/languages';
		$languages = JFactory::getLanguage()->getKnownLanguages();
		
		$files = $parent->manifest->files;
		$installed_langs_html = '<div style="inline-block;"><ul>';
		
		foreach ($languages as $language) {
			$search = JFolder::folders($source, $language['tag']); // no .zip files use "folders" instead
			if (empty($search)) continue;
			
			// Generate something like <file type="file" id="lang_pf4_en-GB">en-GB</file>
			$file = $files->addChild('file', array_pop($search));
			$file->addAttribute('type', 'file');
			$file->addAttribute('id', $this->name . '_' . $language['tag']);
			$installed_langs_html .= '<li>' . sprintf('<b>%s - %s</b>', $language['tag'], $language['name']);
			if(in_array($language['tag'], $this->uncomplete_lang)) {
				$installed_langs_html .= ' ... <span style="color: darkorange;">(' . sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_NOTFULL_TRANSLATED'), '<a href="https://github.com/projectfork/Translations/wiki" target="_blank">Projectfork Translations Team</a>') . ')';
			}
			$installed_langs_html .= '</li>';
		}
		$installed_langs_html .= '</ul></div>';
		
		if (empty($files)) {
			// No packages to install: replace failure message with something that's more descriptive.
			$app->enqueueMessage(sprintf(JText::_('PKG_PROJECTFORK_LANGUAGES_ENGLISH_ONLY')), 'notice');
			return false;
		} else {
			// Override XML-DIV-NOPF-Installer-Placeholder if PF is installed
			$success_html_output = '<div style="display: inline-block; margin-bottom: 25px;">';
			$success_html_output .= '<div style="background: url(https://projectfork.net/templates/hydra/img/logo.png) no-repeat 50% 50% #698C00; height: 63px; width: 205px; border: 6px solid #567300; padding: 10px; border-top-left-radius: 5px; border-bottom-left-radius: 5px; float: left;"></div>';
			$success_html_output .= '<div style="background-color: #698C00; height: 63px; border-width: 6px; border-style: solid; border-color: #567300; border-left: 0px; padding: 10px; color: white; font-size: 25px; line-height: 60px; float: left; text-indent: 0; text-align: center; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">' . JText::_('PKG_PROJECTFORK_LANGUAGES_INSTALLED_DETECTED') . '</div>';
			$success_html_output .= '</div>';
			echo $success_html_output;
			echo $installed_langs_html;
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