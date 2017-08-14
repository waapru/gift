<?php

class shopGiftPlugin extends shopPlugin
{
	// event: frontendCart
	public function frontendCart()
	{
		$html = '';
		if ( $this->getSettings('on') )
		{
			$response = waSystem::getInstance()->getResponse();
			$aurl = 'plugins/gift/js/arcticmodal/';
			$response->addCss($aurl.'jquery.arcticmodal-0.3.css','shop');
			$response->addCss($aurl.'themes/simple.css','shop');
			$response->addJs($aurl.'jquery.arcticmodal-0.3.min.js','shop');
			$response->addJs('wa-content/js/jquery-plugins/jquery.cookie.js');
			
			$f = new shopGiftPluginFiles;
			$f->addCss('css');
			$f->addJs('js');
			$url = wa()->getAppUrl();
			$html = '<div id="gift-p-list-wr" data-url="'.$url.'"></div>';
		}
		return $html;
	}
	
	// event: frontend_checkout
	public function frontendCheckout()
	{
		$html = '';
		if ( $this->getSettings('checkout') )
			$html = $this->frontendCart();
		return $html;
	}
	
	// event: order_action.create
	public function orderActionCreate($data)
	{
		if ( !$this->getSettings('on') )
			return;
		
		$order_id = $data['order_id'];
		$model = new shopOrderItemsModel;
		$o = new shopGiftPluginOrder;
		$h = new shopGiftPluginHelper;
		$gifts = array();
		$items_gifts = $h->getItemsGifts();
		if ( !empty($items_gifts) )
			foreach ( $items_gifts as $product_id => $v )
				if ( !empty($v['gifts']) )
					foreach ( $v['gifts'] as $gift )
						if ( isset($gifts[$gift['id']]) )
							$gifts[$gift['id']]['quantity'] += $gift['quantity'];
						else
							$gifts[$gift['id']] = $gift;
		
		if ( !empty($gifts) )
		{
			foreach ( $gifts as $k=>$gift )
				if ( $gift['quantity'] == 0 )
					unset($gifts[$k]);
			
			foreach ( $gifts as $gift )
				$o->insertOrderItem($gift,$order_id);
			
			$o->reduceProductsFromStocks($order_id,array_keys($gifts));
			$o->sendNotification($data);
		}
	}
	
	// event: backend_product
	public function backendProduct($product)
	{
		$html = '';
		
		$product_id = $product->id;
		
		$model = new shopGiftPluginProductGiftModel;
		$selected_gifts = $model->where('product_id = '.(int)$product_id)->order('`order` ASC')->fetchAll('gift_id');
		
		$m = new shopGiftPluginParamsModel;
		$params = $m->getByProductId($product_id);
		
		$h = new shopGiftPluginHelper(false);
		$gift_list = $h->getGiftList();
		if ( !empty($gift_list) )
		{
			$ids = array_keys($gift_list);
			if ( !empty($selected_gifts) )
				foreach ( $selected_gifts as $id=>$v )
					if ( !in_array($id,$ids) )
					{
						$model->query('DELETE FROM '.$model->getTableName().' WHERE gift_id='.(int)$id.' AND product_id = '.(int)$product_id);
						unset($selected_gifts[$k]);
					}
		}
		$stcks = false;
		$sm = new shopStockModel;
		if ( $r = $sm->getAll('id') )
			foreach ( $r as $id=>$v )
				$stcks[$id] = $v['name'];
		
		$view = wa()->getView();
		$view->assign(compact('gift_list','product_id','selected_gifts','params','stcks'));
		$html = $view->fetch($this->path.'/templates/toolbar.html');
		return array(
			'toolbar_section' => $html,
		);
	}
	
	
	public function getPath()
	{
		return $this->path;
	}
	
	
	/* HELPER */
	static public function gifts($product_id,$plugin = null)
	{
		$html = '';
		if ( $plugin == null )
			$plugin = wa()->getPlugin('gift');
		if ( $plugin->getSettings('on') )
		{
			$list = new shopGiftPluginHelper;
			$gifts = $list->getGifts($product_id);
			
			if ( !empty($gifts) )
			{
				$img_url = $plugin->getPluginStaticUrl().'img/gift36.png';
				$view = wa()->getView();
				$view->assign('gifts',$gifts);
				$view->assign('img_url',$img_url);
				
				$f = new shopGiftPluginFiles;
				$f->addCss('css');
				$f->addJs('js');
				$html = $view->fetch('string:'.$f->getFileContent('gift'));
			}
		}
		return $html;
	}
	
	/* HELPER */
	static public function products($gift_id,$product_id=0,$plugin = null)
	{
		$html = '';
		if ( $plugin == null )
			$plugin = wa()->getPlugin('gift');
		if ( $plugin->getSettings('on') )
		{
			$gp = new shopGiftPluginGiftProducts($plugin->getSettings('max_product_count'));
			if ( !empty($gift_id) )
				$products = $gp->getByGiftId($gift_id);
			elseif ( $product_id > 0 )
				$products = $gp->getByProductId($product_id);
			
			if ( !empty($products) )
			{
				$view = wa()->getView();
				$view->assign('products',$products);
				$view->assign('gift_id',$gift_id);
				
				$f = new shopGiftPluginFiles;
				$f->addCss('css');
				$f->addJs('js');
				$html = $view->fetch('string:'.$f->getFileContent('products'));
			}
		}
		return $html;
	}
	
	// event: frontend_product
	public function frontendProduct($product)
	{
		$html = '';
		if ( $this->getSettings('on') )
		{
			$html_gifts = ( $this->getSettings('gift') ) ? self::gifts($product->id,$this) : '';
			$html_products = ( $this->getSettings('products') ) ? self::products(0,$product->id,$this) : '';
		}
		return array(
			'block'=> $html_gifts.$html_products
		);
	}
	
	
	// event: backend_products
	public function backendProducts()
	{
		/* $m = new shopPreorderPluginOrderModel;
		$count = $m->getCount();
		return array(
			'sidebar_top_li' => '<li id="s-action-preorder"><span class="count">'.$count.'</span><a href="#/preorderProducts/"><i class="icon16" style="background-image: url(\''.$this->getPluginStaticUrl().'img/preorder.png\');"></i>Предзаказанные товары</a>
			<script src="'.$this->getPluginStaticUrl().'js/products.js?v'.$this->info['version'].'"></script>
			</li>'
		); */
	}
	
	
	static function lists()
	{
		$lists = array();
		$m = new shopSetModel;
		if ( $r = $m->getAll()/*getByField('type',0,true)*/ )
			foreach ( $r as $v )
				$lists[$v['id']] = $v['name'];
		return $lists;
	}

}