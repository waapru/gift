<?php

class shopGiftPluginSetGiftController extends waJsonController
{

	public function execute()
	{
		if ( !waRequest::isXMLHttpRequest() )
			throw new waException('Page not found', 404);
		
		$product_id = waRequest::post('product_id',0,waRequest::TYPE_INT);
		$ids = waRequest::post('gift_id',array(),waRequest::TYPE_ARRAY_INT);
		$qty = waRequest::post('gift_qty',array());
		$gift_param = waRequest::post('gift_param',array());
		
		$model = new shopGiftPluginProductGiftModel;
		$model->setGifts($product_id,$ids,$qty);
		
		foreach ( array('start','finish') as $v )
			if ( preg_match('/(\d\d)\.(\d\d)\.(\d\d\d\d)/i',$gift_param[$v],$m) )
				$gift_param[$v] = $m[3].'-'.$m[2].'-'.$m[1];
			else
			{
				$gift_param['start'] = $gift_param['finish'] = null;
				break;
			}
		
		$m = new shopGiftPluginParamsModel;
		$a = compact(product_id);
		if ( $r = $m->getByField($a) )
			$m->updateById($r['id'],$gift_param);
		else
			$m->insert($gift_param+$a);
	}

}