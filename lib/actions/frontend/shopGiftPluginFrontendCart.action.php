<?php

class shopGiftPluginFrontendCartAction extends waViewAction
{

	public function execute()
	{
		$h = new shopGiftPluginHelper;
		$gifts = $h->getItemsGifts();
		
		$gift_products_block = '';
		$plugin = wa()->getPlugin('gift');
		if ( ( $plugin->getSettings('products') ) )
		{
			$gift_ids = array();
			if ( !empty($gifts) )
				foreach ( $gifts as $item )
					if ( !empty($item['gifts']) )
						foreach ( $item['gifts'] as $gift )
							$gift_ids[] = $gift['id'];
			$gift_products_block = shopGiftPlugin::products($gift_ids);
		}
		$this->view->assign(compact('gifts','gift_products_block'));
	}

}