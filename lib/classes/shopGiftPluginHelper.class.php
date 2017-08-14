<?php

class shopGiftPluginHelper
{
	protected $_gifts = array();
	protected $_cart_items = array();
	protected $_cart_item_quantities = array();
	protected $_ignore_stock_count;
	protected $_cart_gifts = array();
	protected $_quantities = array();
	protected $_storage;
	
	public function __construct($init_cart = true)
	{
		if ( $init_cart )
		{
			$cart = new shopCart();
			$this->_total = $cart->total(false);
			$this->_code = $cart->getCode();
			$this->_cart_items = $cart->items(false);
			if ( !empty($this->_cart_items) )
				foreach ( $this->_cart_items as $item )
					$this->_cart_item_quantities[$item['product_id']] = $item['quantity'];
			if ( !empty($this->_cart_items) )
				foreach ( $this->_cart_items as $k=>$item )
					if ( $item['type'] != 'product' )
						unset($this->_cart_items[$k]);
		}
		$app_settings_model = new waAppSettingsModel();
		$this->_ignore_stock_count = $app_settings_model->get('shop', 'ignore_stock_count');
		
		$this->_storage = wa()->getStorage();
		$this->_cart_gifts = $this->_storage->read('shopGiftPlugin');
	}
	
	
	public function getItems()
	{
		return $this->_cart_items;
	}
	
	
	public function getGiftList()
	{
		if ( empty($this->_gifts) )
		{
			$plugin = waSystem::getInstance('shop')->getPlugin('gift');
			$collection = new shopProductsCollection('set/'.$plugin->getSettings('list_id'));
			$this->_gifts = $collection->getProducts('*');
		}
		return $this->_gifts;
	}
	
	
	public function getProducts($product_id)
	{
		$products = array();
		$model = new shopGiftPluginProductGiftModel;
		$product_ids = $model->getProductIds($gift_id);
		if ( !in_array($gift_id,$product_ids) )
			$product_ids[] = $gift_id;
		if ( !empty($product_ids) )
		{
			$collection = new shopProductsCollection($product_ids);
			$products = $collection->getProducts('*');
		}
		return $products;
	}
	
	
	public function getItemsGifts()
	{
		$items_gifts = array();
		if ( !empty($this->_cart_items) )
		{
			$iq = array();
			foreach ( $this->_cart_items as $id=>$i )
			{
				$pid = $i['product_id'];
				$q = $i['quantity'];
				if ( !isset($iq[$pid]) )
					$iq[$pid] = array(
						'quantity' => $q,
						'product' => $i['product'],
						'id' => $id,
					);
				else
					$iq[$pid]['quantity'] += $q;
			}
			
			$model = new shopGiftPluginProductGiftModel;
			$items_gifts = $model->getGifts(array_keys($iq));
			$list = $this->getGiftList();
			
			if ( !empty($items_gifts) && !$this->_ignore_stock_count )
				foreach ( $items_gifts as $pid=>$a )
					foreach ( $a['gifts'] as $k=>$g )
						if ( isset($iq[$g['gift_id']]['quantity']) )
							$items_gifts[$pid]['gifts'][$k]['count'] -= $iq[$g['gift_id']]['quantity']; // могут быть лишние модификации
			
			if ( !empty($items_gifts) && !empty($list) )
				foreach ( $items_gifts as $product_id=>&$a )
				{
					$a['item'] = $iq[$product_id];
					$set = ifset($a['params']['set'],0);
					$a['max_quantity'] = ifset($a['params']['qty'],0) ? 1 : $iq[$product_id]['quantity'];
					$gifts = array();
					if ( isset($a['gifts']) )
						foreach ( $a['gifts'] as $k=>$g )
						{
							$id = $g['gift_id'];
							if ( isset($list[$id]) )
							{
								$gifts[$id] = $list[$id] + array(
									'max_quantity' => $a['max_quantity'],
									'quantity' => $set ? $a['max_quantity']*$g['quantity'] : ifset($this->_cart_gifts[$product_id][$id],0),
									'stock_id' => $g['stock_id'] 
								);
								if ( !$this->_ignore_stock_count && $g['count'] != null)
								{
									if ( $g['count'] < $gifts[$id]['quantity'] )
									{
										$gifts = array();
										break;
									}
									else
										$g['count'] -= $gifts[$id]['quantity'];
								}
							}
						}
					if ( empty($gifts) )
						unset($items_gifts[$product_id]);
					else
						$a['gifts'] = $gifts;
					$this->setStorageData($product_id,$a);
				}
		}
		return $items_gifts;
	}
	
	
	public function setStorageData($product_id,$g,$data = false)
	{
		if ( $data === false && isset($this->_cart_gifts[$product_id]) )
			$data = $this->_cart_gifts[$product_id];
		
		if ( $data )
		{
			$total = 0;
			if ( !empty($g) )
			{
				foreach ( $data as $id=>&$q )
					if ( in_array($id,array_keys($g['gifts'])) )
					{
						$m = $g['gifts'][$id]['max_quantity'];
						$q = (int)$q;
						$q = $q>$m ? $m : $q;
						if ( $total + $q > $g['max_quantity'] )
							$q = $g['max_quantity'] - $total;
						$total += $q;
					}
			}
			else
				$data = array();
		}
		
		$this->_cart_gifts[$product_id] = $data;
		$this->_storage->write('shopGiftPlugin',$this->_cart_gifts);
	}
	
	
	public function getGifts($product_id)
	{
		$model = new shopGiftPluginProductGiftModel;
		$ids = $model->getGiftIds($product_id);
		
		$list = $this->getGiftList();
		$gifts = array();
		if ( !empty($list) && !empty($ids) )
		{
			$app_settings_model = new waAppSettingsModel();
			foreach ( $ids as $id )
				if ( isset($list[$id]) )
				{
					$p = $list[$id];
					if ( ( !$this->_ignore_stock_count && $p['count'] ) || $this->_ignore_stock_count || $p['count'] === null )
						$gifts[$id] = $p;
				}
		}
		
		return $gifts;
	}
	
	
	public function getCartGifts()
	{
		$gifts = array();
		$i = array();// счетчик общего количества каждого подарка в отдельности
		if ( !empty($this->_cart_items) )
			foreach ( $this->_cart_items as $item )
			{
				$product_id = $item['product_id'];
				$product_gifts = $this->getGifts($product_id);
				if ( !empty($product_gifts) )
				{
					$gifts[$product_id] = array(
						'name' => $item['name'],
						'quantity' => $item['quantity'],
						'gifts' => array()
					);
					$j = 0;// счетчик количества подарков для текущего элемента корзины, значение не должно превышать кол-во элемента
					foreach ( $product_gifts as $gift )
					{
						$id = $gift['id'];
						if ( !isset($i[$id]) )
							$i[$id] = 0;
						if ( !$this->_ignore_stock_count && $gift['count'] !== null )
						{
							$gift['max_quantity'] = ( $gift['count'] >= $item['quantity'] ) ? $item['quantity'] : $gift['count'];
							if ( isset($this->_cart_item_quantities[$id]) )
							{
								$d = ( $gift['max_quantity'] + $this->_cart_item_quantities[$id] ) - $gift['count'];
								if ( $d > 0  )
									$gift['max_quantity'] -= $d;
							}
							if ( $gift['max_quantity'] < 0 )
								$gift['max_quantity'] = 0;
						}
						else
							$gift['max_quantity'] = $item['quantity'];
						if ( isset($this->_cart_gifts[$product_id][$gift['id']]) )
						{
							$q = $this->_cart_gifts[$product_id][$gift['id']];
							$qty = ( $q <= $gift['max_quantity'] ) ? $q : $gift['max_quantity'];
							$i[$id] += $qty;
							$j += $qty;
							
							// если превышено доступное кол-во подарка или количесвто элемента корзины, то уменьшаем $qty
							if ( $j > $item['quantity'] || $i[$id] > $gift['count'] )
							{
								$dj = $j - $item['quantity'];
								$di = $i[$id] - $gift['max_quantity'];
								$qty -= ( $dj >= $di ) ? $dj : $di;
							}
							$gift['quantity'] = $qty;
						}
						else
							$gift['quantity'] = 0;
						$gifts[$product_id]['gifts'][] = $gift;
					}
					if ( empty($gifts[$product_id]['gifts']) )
						unset($gifts[$product_id]);
				}
			}
		
		return $this->_autoAdd($gifts);
	}
	
	
	protected function _getRemain($gifts,$quantity)
	{
		$remain = 0;
		if ( !empty($gifts) )
		{
			$count = 0;
			foreach ( $gifts as $g )
				$count += $g['quantity'];
			$remain = ( $count < $quantity ) ? $quantity-$count : 0;
		}
		return $remain;
	}
	
	
	protected function _autoAdd($gifts)
	{
		if ( !empty($gifts) )
			foreach ( $gifts as $product_id=>$v )
				if ( $remain = $this->_getRemain($v['gifts'],$v['quantity']) )
					foreach ( $v['gifts'] as $k=>$g )
					{
						if ( $remain > 0 && !isset($this->_cart_gifts[$product_id][$g['id']]) )
						{
							if ( !$this->_ignore_stock_count && $g['count'] !== null )
								$c = ( $g['count'] >= $remain ) ? $remain : $g['count'];
							else
								$c = $remain;
							$remain -= $c;
							$gifts[$product_id]['gifts'][$k]['quantity'] = $c;
						}
						else
							break;
					}
		return $gifts;
	}

}