<?php

class shopGiftPluginParamsModel extends waModel
{
	protected $table = 'shop_gift_params';
	
	public function getByProductId($product_id)
	{
		if ( $r = $this->getByField(compact('product_id')) )
			foreach ( array('start','finish') as $v )
				$r[$v] = ( $r[$v] != null ) ? date('d.m.Y',strtotime($r[$v])) : '';
		else
			$r = array('set'=>0,'qty'=>0,'start'=>'','finish'=>'','stock_id'=>0);
		
		if ( $r['stock_id'] == 0 )
		{
			$sm = new shopStockModel;
			$stock_id = $sm->select('id')->limit(1)->fetchField('id');
			$r['stock_id'] = $stock_id ? $stock_id : 0;
		}
		return $r;
	}
}