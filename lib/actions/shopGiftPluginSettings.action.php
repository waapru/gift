<?php

class shopGiftPluginSettingsAction extends waViewAction
{

	public function execute()
	{
		$plugin = wa()->getPlugin('gift');
		
		$controls = array(
			'subject' => 'standart',
			'namespace' => 'shop_gift',
			'title_wrapper' => '%s',
			'description_wrapper' => '<br><span class="hint">%s</span>',
			'control_wrapper'     => '<div class="field"><div class="name">%s</div><div class="value">%s%s</div></div>',
		);
		$standart_settings = implode('',$plugin->getControls($controls));
		
		$f = new shopGiftPluginFiles;
		$themes = $f->getThemes();
		
		$this->view->assign(compact('standart_settings','themes','plugin'));
	}

}