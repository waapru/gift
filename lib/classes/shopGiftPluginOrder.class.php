<?php

class shopGiftPluginOrder
{
	private $_ignore_stock_count;
	private $_update_stock_count_on_create_order;
	
	public function __construct()
	{
		$m = new waAppSettingsModel();
		$this->_ignore_stock_count = $m->get('shop', 'ignore_stock_count');
		$this->_update_stock_count_on_create_order = $m->get('shop', 'update_stock_count_on_create_order');
	}
	
	public function insertOrderItem($product,$order_id)
	{
		if ( $order_id > 0 )
		{
			$model = new shopOrderItemsModel;
			
			$fields = array(
				'order_id' => $order_id,
				'sku_id' => $product['sku_id']
			);
			
			if ( $row = $model->getByField($fields) )
				$model->updateById($row['id'],array('quantity'=>$row['quantity']+$product['quantity']));
			else
			{
				$data = array(
					'order_id' => $order_id,
					'name' => $product['name'],
					'product_id' => $product['id'],
					'sku_id' => $product['sku_id'],
					'stock_id' => $product['stock_id'],
					'type' => 'product',
					'price' => 0,
					'quantity' => $product['quantity'],
				);
				$model->insert($data);
			}
		}
	}
	
	
	public function reduceProductsFromStocks($order_id,$gift_ids)
	{
		if ( !$this->_update_stock_count_on_create_order )
			return;
		
		$items_model = new shopOrderItemsModel();
		$items = $items_model->select('*')->where("type='product' AND order_id = ".(int) $order_id)->fetchAll();
		$sku_stock = array();
		foreach ( $items as $item )
			if ( in_array($item['id'],$gift_ids) )
			{
				if ( !isset($sku_stock[$item['sku_id']][$item['stock_id']]) )
					$sku_stock[$item['sku_id']][$item['stock_id']] = 0;
				$sku_stock[$item['sku_id']][$item['stock_id']] -= $item['quantity'];
			}
		
		$items_model->updateStockCount($sku_stock);
		$order_params_model = new shopOrderParamsModel();
		$order_params_model->setOne($order_id, 'gifts_reduced', 1);
	}
	
	
	public function sendNotification($data)
	{
		$order_id = $data['order_id'];
		$data['action_id'] = 'gift';
		$order_model = new shopOrderModel();
		$order = $order_model->getById($order_id);
		$params_model = new shopOrderParamsModel();
		$order['params'] = $params_model->get($order_id);
		// send notifications
		shopNotifications::send('order.gift', array(
			'order' => $order,
			'customer' => new waContact($order['contact_id']),
			'status' => '',
			'action_data' => $data
		));
	}

}