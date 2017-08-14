<?php

class shopGiftPluginFrontendSetController extends waJsonController
{

	public function execute()
	{
		$data = waRequest::post('data',array());
		$product_id = waRequest::post('product_id',0,waRequest::TYPE_INT);
		
		$h = new shopGiftPluginHelper;
		$items_gifts = $h->getItemsGifts();
		$h->setStorageData($product_id,ifset($items_gifts[$product_id],false),$data);
		
		/*$total = 0;
		if ( !empty($items_gifts) && ( $g = ifset($items_gifts[$product_id],false) ) )
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
		
		if ( !empty($data) && $product_id )
		{
			$storage = wa()->getStorage();
			$cart_gifts = $storage->read('shopGiftPlugin');
			$cart_gifts[$product_id] = $data;
			$storage->write('shopGiftPlugin',$cart_gifts);
		}*/
	}

}