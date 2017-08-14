<?php

class shopGiftPluginProductGiftModel extends waModel
{
	protected $table = 'shop_gift_product_gift';
	
	
	public function setGift($product_id,$gift_id)
	{
		$product_id = (int)$product_id;
		$gift_id = (int)$gift_id;
		if ( $product_id > 0 )
		{
			$this->deleteByField('product_id', $product_id);
			if ( $gift_id > 0 )
				$this->insert(array(
					'product_id' => $product_id,
					'gift_id' => $gift_id,
				));
		}
	}
	
	
	public function setGifts($product_id,$ids,$qty = array())
	{
		$product_id = (int)$product_id;
		if ( $product_id > 0 )
		{
			$this->deleteByField('product_id', $product_id);
			if ( !empty($ids) )
				foreach ( $ids as $order=>$gift_id )
				{
					$quantity = 1;
					if ( isset($qty[$gift_id]) )
						$quantity = (int)$qty[$gift_id];
					$quantity = $quantity > 0 ? $quantity : 1;
					$this->insert(array(
						'product_id' => $product_id,
						'gift_id' => $gift_id,
						'order' => $order,
						'quantity' => $quantity,
					));
				}
		}
	}
	
	
	public function getGifts($product_id)
	{
		$gifts = array();
		$product_ids = array();
		
		if ( !empty($product_id) )
		{
			if ( is_array($product_id) )
				$product_ids = $product_id;
			elseif ( $product_id > 0 )
				$product_ids = array($product_id);
		}
		
		if ( !empty($product_ids) )
		{
			$product_ids = array_map('intval',$product_ids);
			$in = implode(',',$product_ids);
			$m = new shopGiftPluginParamsModel;
			$q = "(`start` IS NULL OR `finish` IS NULL OR ( `start` < NOW() AND `finish` > NOW() )) AND product_id IN ($in)";
			if ( $r = $m->where($q)->fetchAll('product_id') )
				foreach ( $r as $product_id=>$v )
				{
					$gifts[$product_id]['params'] = array(
						'qty' => $v['qty'],
						'set' => $v['set'],
					);
					$q = "
						SELECT g.*, p.sku_id, sk.count, st.count as stock_count, st.stock_id 
						FROM shop_gift_product_gift g
						LEFT JOIN shop_product p ON p.id=g.gift_id
						LEFT JOIN shop_product_skus as sk ON sk.id = p.sku_id
						LEFT JOIN shop_product_stocks as st ON st.sku_id = sk.id
						WHERE g.product_id = ".(int)$product_id." AND ( st.stock_id = ".(int)$v['stock_id']." OR  st.stock_id IS NULL )
					";
					if ( $rr = $this->query($q)->fetchAll() )
						foreach ( $rr as $w )
							$gifts[$product_id]['gifts'][$w['order']] = array(
								'gift_id' => $w['gift_id'],
								'quantity' => $w['quantity'],
								'count' => $w['stock_count'] == null ? $w['count'] : $w['stock_count'],
								'stock_id' => $w['stock_id']
							);
				}
		}
		
		if ( !empty($gifts) )
			foreach ( $gifts as &$v )
				ksort($v['gifts']);
		
		return $gifts;
	}
	
	
	public function getGiftIds($product_id)
	{
		$ids = array();
		$product_ids = array();
		
		if ( !empty($product_id) )
		{
			if ( is_array($product_id) )
				$product_ids = $product_id;
			elseif ( $product_id > 0 )
				$product_ids = array($product_id);
		}
		
		if ( !empty($product_ids) )
		{
			$product_ids = array_map('intval',$product_ids);
			$in = implode(',',$product_ids);
			$m = new shopGiftPluginParamsModel;
			$q = "(`start` IS NULL OR `finish` IS NULL OR ( `start` < NOW() AND `finish` > NOW() )) AND product_id IN ($in)";
			$product_ids = ( $r = $m->select('product_id')->where($q)->fetchAll('product_id') ) ? array_keys($r) : array();
		}
		if ( !empty($product_ids) )
		{
			$r = $this->select('gift_id')->where("product_id IN ($in)");
			if ( count($product_ids) == 1 )
				$records = $r->order('`order` ASC')->fetchAll();
			else
				$records = $r->fetchAll();
			
			$k = array();
			foreach ( $records as $v )
				$k[$v['gift_id']] = 1;
			
			$ids = array_keys($k);
		}
		return $ids;
	}
	
	
	public function getProductIds($gift_id)
	{
		$ids = array();
		$gift_ids = array();
		
		if ( !empty($gift_id) )
		{
			if ( is_array($gift_id) )
				$gift_ids = $gift_id;
			elseif ( $gift_id > 0 )
				$gift_ids = array($gift_id);
		}
		
		if ( !empty($gift_ids) )
		{
			$gift_ids = array_map('intval',$gift_ids);
			$in = implode(',',$gift_ids);
			$records = $this->where("gift_id IN ($in)")->fetchAll();
			
			$k = array();
			foreach ( $records as $v )
				$k[$v['product_id']] = 1;
			
			$ids = array_keys($k);
		}
		return $ids;
	}
	
	
	public function addField()
	{
		if ( $this->query("SHOW COLUMNS FROM shop_gift_product_gift LIKE 'quantity'")->count() == 0 )
			$this->query("ALTER TABLE shop_gift_product_gift ADD quantity INT DEFAULT 1");
	}
}