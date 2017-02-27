<?php
/*
* The MIT License (MIT)
*
* Copyright (c) 2016 Benichou
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
*  @author    Benichou <benichou.software@gmail.com>
*  @copyright 2016 Benichou
*  @license   http://opensource.org/licenses/MIT  The MIT License (MIT)
*/

if (!defined('_PS_VERSION_'))
	exit;

class GTagManager extends Module
{
	public function __construct()
	{
		$this->name = 'gtagmanager';
		$this->tab = 'analytics_stats';
		$this->author = 'Benichou';
		$this->version = '1.0';
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('Google Tag Manager');
		$this->description = $this->l('Adding Google Tag Manager script');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('header') || !$this->registerHook('top'))
			return false;

		return true;
	}

	public function uninstall()
	{
		if(!$this->unregisterHook('header') || !$this->unregisterHook('top'))
			return false;

		Configuration::deleteByName('GOOGLE_TAG_MANAGER_ID');
		return parent::uninstall();
	}

	public function getContent()
	{
		$output = '';

		// If form has been sent
		if (Tools::isSubmit('submit'.$this->name))
		{
			Configuration::updateValue('GOOGLE_TAG_MANAGER_ID', Tools::getValue('GOOGLE_TAG_MANAGER_ID'));
			$output .= $this->displayConfirmation($this->l('Settings updated successfully'));
		}

		$output .= $this->renderForm();
		return $output;
	}

	public function renderForm()
	{
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->submit_action = 'submit'.$this->name;

		$fields_forms = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('General settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Tag Manager ID'),
						'name' => 'GOOGLE_TAG_MANAGER_ID',
						'size' => 20,
						'required' => true,
						'hint' => $this->l('Enter here your ID (GTM-XXXXXX).')
					)
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			)
		);

		// Load current value
		$helper->fields_value['GOOGLE_TAG_MANAGER_ID'] = Configuration::get('GOOGLE_TAG_MANAGER_ID');

		return $helper->generateForm(array($fields_forms));
	}

	public function hookHeader($params)
	{
		$tag_manager_id = Tools::safeOutput(Configuration::get('GOOGLE_TAG_MANAGER_ID'));
		if (!$tag_manager_id)
			return;

		return '<script data-keepinline="true">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\'//www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\''.$tag_manager_id.'\');</script>';
	}

	public function hookTop($params)
	{
		$tag_manager_id = Tools::safeOutput(Configuration::get('GOOGLE_TAG_MANAGER_ID'));
		if (!$tag_manager_id)
			return;

		$this->context->smarty->assign(array(
			'google_tag_manager_id' => $tag_manager_id
		));

		return $this->display(__FILE__, 'noscript.tpl');
	}
}
?>
