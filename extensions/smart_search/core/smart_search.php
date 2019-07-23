<?php


if (! defined ( 'DIR_CORE' )) {
header ( 'Location: static_pages/' );
}

class ExtensionSmartSearch extends Extension {

	public function onControllerPagesProductSearch_UpdateData($param){
		$this->baseObject->loadModel('catalog/ssproduct');
		$this->loadModel('catalog/category');
		$request = $this->baseObject->request->get;
		$url = '';

		//is this an embed mode
        if ($this->baseObject->config->get('embed_mode') == true) {
            $cart_rt = 'r/checkout/cart/embed';
        } else {
            $cart_rt = 'checkout/cart';
        }

        if (isset($request['keyword'])) {
            $url .= '&keyword='.$request['keyword'];
        }

        if (isset($request['category_id'])) {
            $url .= '&category_id='.$request['category_id'];
        }

        if (isset($request['description'])) {
            $url .= '&description='.$request['description'];
        }

        if (isset($request['model'])) {
            $url .= '&model='.$request['model'];
        }

        if (isset($request['sort'])) {
            $url .= '&sort='.$request['sort'];
        }

        if (isset($request['order'])) {
            $url .= '&order='.$request['order'];
        }

        if (isset($request['page'])) {
            $url .= '&page='.$request['page'];
        }
        if (isset($request['limit'])) {
            $url .= '&limit='.$request['limit'];
        }
        if (isset($request['page'])) {
            $page = $request['page'];
        } else {
            $page = 1;
        }	

        if (isset($request['sort'])) {
            $sorting_href = $request['sort'];
        } else {
            $sorting_href = $this->baseObject->config->get('config_product_default_sort_order');
        }

        list($sort,$order) = explode("-",$sorting_href);
        
        if ($sort == 'name') {
            $sort = 'pd.'.$sort;
        } elseif (in_array($sort, array('sort_order', 'price'))) {
            $sort = 'p.'.$sort;
        }
        if (isset($request['category_id'])) {
            $category_id = explode(',', $request['category_id']);
            end($category_id);
            $category_id = current($category_id);
        } else {
            $category_id = '';
        }
         $promotion = new APromotion();
         $limit = $this->baseObject->config->get('config_catalog_limit');
                if (isset($request['limit']) && intval($request['limit']) > 0) {
                    $limit = intval($request['limit']);
                    if ($limit > 50) {
                        $limit = 50;
                    }
                }

                $product_total = $this->baseObject->model_catalog_ssproduct->getTotalProductsByKeyword(
                                                                    $request['keyword'],
                                                                    $category_id,
                                                                    isset($request['description']) ? $request['description'] : '',
                                                                    isset($request['model']) ? $request['model'] : '');

        // $product_total = $this->baseObject->model_catalog_product->getProductsByKeyword();$products = array();
                $products_result = $this->baseObject->model_catalog_ssproduct->getProductsByKeyword($request['keyword'],
                    $category_id,
                    isset($request['description']) ? $request['description'] : '',
                    isset($request['model']) ? $request['model'] : '',
                    $sort,
                    $order,
                    ($page - 1) * $limit,
                    $limit
                );


//if single result, redirect to the product
                if (count($products_result) == 1) {
                    // redirect(
                    //     $this->baseObject->html->getSEOURL(
                    //         'product/product',
                    //         '&product_id='.key($products_result),
                    //         '&encode'
                    //     )
                    // );
                }

				$this->loadModel('catalog/review');
                $this->loadModel('tool/seo_url');
                if (is_array($products_result) && $products_result) {
                    $product_ids = array();
                    foreach ($products_result['products']['prod1'] as $result) {
                        $product_ids[] = (int)$result['product_id'];
                    }
                    foreach ($products_result['products']['prod2'] as $result) {
                        $product_ids[] = (int)$result['product_id'];
                    }
                    foreach ($products_result['products']['prod3'] as $result) {
                        $product_ids[] = (int)$result['product_id'];
                    }

                    //Format product data specific for confirmation page
                    $resource = new AResource('image');
                    $thumbnails = $resource->getMainThumbList(
                        'products',
                        $product_ids,
                        $this->baseObject->config->get('config_image_product_width'),
                        $this->baseObject->config->get('config_image_product_height')
                    );
                    $stock_info = $this->baseObject->model_catalog_product->getProductsStockInfo($product_ids);

                    // $products = array('prod1' => array(), 'prod2' => array(), 'prod3' => array(),);
                    foreach ($products_result['products']['prod1'] as $result) {
                        $thumbnail = $thumbnails[$result['product_id']];
                        if ($this->baseObject->config->get('enable_reviews')) {
                        	// print_r($result);
                        	$rating = false;
                            // $rating = $this->baseObject->model_catalog_review->getAverageRating($result['product_id']);
                        } else {
                            $rating = false;
                        }

                        $special = false;

                        $discount = $promotion->getProductDiscount($result['product_id']);

                        if ($discount) {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $discount,
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                        } else {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $result['price'],
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                            $special = $promotion->getProductSpecial($result['product_id']);
                            if ($special) {
                                $special = $this->baseObject->currency->format(
                                    $this->baseObject->tax->calculate(
                                        $special,
                                        $result['tax_class_id'],
                                        $this->baseObject->config->get('config_tax')
                                    )
                                );
                            }
                        }

                        $options = $this->baseObject->model_catalog_product->getProductOptions($result['product_id']);
                        if ($options) {
                            $add = $this->baseObject->html->getSEOURL(
                                'product/product',
                                '&product_id='.$result['product_id'],
                                '&encode'
                            );
                        } else {
                            if ($this->baseObject->config->get('config_cart_ajax')) {
                                $add = '#';
                            } else {
                                $add = $this->baseObject->html->getSecureURL(
                                    $cart_rt,
                                    '&product_id='.$result['product_id'],
                                    '&encode'
                                );
                            }
                        }

                        //check for stock status, availability and config
                        $track_stock = false;
                        $in_stock = false;
                        $no_stock_text = $this->baseObject->language->get('text_out_of_stock');
                        $stock_checkout = $result['stock_checkout'] === ''
                                        ? $this->baseObject->config->get('config_stock_checkout')
                                        : $result['stock_checkout'];
                        $total_quantity = 0;
                        if ($stock_info[$result['product_id']]['subtract']) {
                            $track_stock = true;
                            $total_quantity = $stock_info[$result['product_id']]['quantity'];
                            //we have stock or out of stock checkout is allowed
                            if ($total_quantity > 0 || $stock_checkout) {
                                $in_stock = true;
                            }
                        }

                        $products['prod1'][] = array(
                            'product_id'     => $result['product_id'],
                            'name'           => $result['name'],
                            'blurb'          => $result['blurb'],
                            'model'          => $result['model'],
                            'rating'         => $rating,
                            'stars'          => sprintf($this->baseObject->language->get('text_stars'), $rating),
                            'thumb'          => $thumbnail,
                            'price'          => $price,
                            'raw_price'      => $result['price'],
                            'call_to_order'  => $result['call_to_order'],
                            'options'        => $options,
                            'special'        => $special,
                            'href'           =>
                                $this->baseObject->html->getSEOURL(
                                    'product/product',
                                    '&keyword='.$request['keyword'].$url.'&product_id='.$result['product_id'],
                                    '&encode'
                            ),
                            'add'            => $add,
                            'description'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                            'track_stock'    => $track_stock,
                            'in_stock'       => $in_stock,
                            'no_stock_text'  => $no_stock_text,
                            'total_quantity' => $total_quantity,
                            'tax_class_id'   => $result['tax_class_id'],
                        );
                    }

                    foreach ($products_result['products']['prod2'] as $result) {
                        $thumbnail = $thumbnails[$result['product_id']];
                        if ($this->baseObject->config->get('enable_reviews')) {
                            // print_r($result);
                            $rating = false;
                            // $rating = $this->baseObject->model_catalog_review->getAverageRating($result['product_id']);
                        } else {
                            $rating = false;
                        }

                        $special = false;

                        $discount = $promotion->getProductDiscount($result['product_id']);

                        if ($discount) {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $discount,
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                        } else {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $result['price'],
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                            $special = $promotion->getProductSpecial($result['product_id']);
                            if ($special) {
                                $special = $this->baseObject->currency->format(
                                    $this->baseObject->tax->calculate(
                                        $special,
                                        $result['tax_class_id'],
                                        $this->baseObject->config->get('config_tax')
                                    )
                                );
                            }
                        }

                        $options = $this->baseObject->model_catalog_product->getProductOptions($result['product_id']);
                        if ($options) {
                            $add = $this->baseObject->html->getSEOURL(
                                'product/product',
                                '&product_id='.$result['product_id'],
                                '&encode'
                            );
                        } else {
                            if ($this->baseObject->config->get('config_cart_ajax')) {
                                $add = '#';
                            } else {
                                $add = $this->baseObject->html->getSecureURL(
                                    $cart_rt,
                                    '&product_id='.$result['product_id'],
                                    '&encode'
                                );
                            }
                        }

                        //check for stock status, availability and config
                        $track_stock = false;
                        $in_stock = false;
                        $no_stock_text = $this->baseObject->language->get('text_out_of_stock');
                        $stock_checkout = $result['stock_checkout'] === ''
                                        ? $this->baseObject->config->get('config_stock_checkout')
                                        : $result['stock_checkout'];
                        $total_quantity = 0;
                        if ($stock_info[$result['product_id']]['subtract']) {
                            $track_stock = true;
                            $total_quantity = $stock_info[$result['product_id']]['quantity'];
                            //we have stock or out of stock checkout is allowed
                            if ($total_quantity > 0 || $stock_checkout) {
                                $in_stock = true;
                            }
                        }

                        $products['prod2'][] = array(
                            'product_id'     => $result['product_id'],
                            'name'           => $result['name'],
                            'blurb'          => $result['blurb'],
                            'model'          => $result['model'],
                            'rating'         => $rating,
                            'stars'          => sprintf($this->baseObject->language->get('text_stars'), $rating),
                            'thumb'          => $thumbnail,
                            'price'          => $price,
                            'raw_price'      => $result['price'],
                            'call_to_order'  => $result['call_to_order'],
                            'options'        => $options,
                            'special'        => $special,
                            'href'           =>
                                $this->baseObject->html->getSEOURL(
                                    'product/product',
                                    '&keyword='.$request['keyword'].$url.'&product_id='.$result['product_id'],
                                    '&encode'
                            ),
                            'add'            => $add,
                            'description'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                            'track_stock'    => $track_stock,
                            'in_stock'       => $in_stock,
                            'no_stock_text'  => $no_stock_text,
                            'total_quantity' => $total_quantity,
                            'tax_class_id'   => $result['tax_class_id'],
                        );
                    }

                    foreach ($products_result['products']['prod3'] as $result) {
                        $thumbnail = $thumbnails[$result['product_id']];
                        if ($this->baseObject->config->get('enable_reviews')) {
                            // print_r($result);
                            $rating = false;
                            // $rating = $this->baseObject->model_catalog_review->getAverageRating($result['product_id']);
                        } else {
                            $rating = false;
                        }

                        $special = false;

                        $discount = $promotion->getProductDiscount($result['product_id']);

                        if ($discount) {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $discount,
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                        } else {
                            $price = $this->baseObject->currency->format(
                                $this->baseObject->tax->calculate(
                                    $result['price'],
                                    $result['tax_class_id'],
                                    $this->baseObject->config->get('config_tax')
                                )
                            );
                            $special = $promotion->getProductSpecial($result['product_id']);
                            if ($special) {
                                $special = $this->baseObject->currency->format(
                                    $this->baseObject->tax->calculate(
                                        $special,
                                        $result['tax_class_id'],
                                        $this->baseObject->config->get('config_tax')
                                    )
                                );
                            }
                        }

                        $options = $this->baseObject->model_catalog_product->getProductOptions($result['product_id']);
                        if ($options) {
                            $add = $this->baseObject->html->getSEOURL(
                                'product/product',
                                '&product_id='.$result['product_id'],
                                '&encode'
                            );
                        } else {
                            if ($this->baseObject->config->get('config_cart_ajax')) {
                                $add = '#';
                            } else {
                                $add = $this->baseObject->html->getSecureURL(
                                    $cart_rt,
                                    '&product_id='.$result['product_id'],
                                    '&encode'
                                );
                            }
                        }
          
                        //check for stock status, availability and config
                        $track_stock = false;
                        $in_stock = false;
                        $no_stock_text = $this->baseObject->language->get('text_out_of_stock');
                        $stock_checkout = $result['stock_checkout'] === ''
                                        ? $this->baseObject->config->get('config_stock_checkout')
                                        : $result['stock_checkout'];
                        $total_quantity = 0;
                        if ($stock_info[$result['product_id']]['subtract']) {
                            $track_stock = true;
                            $total_quantity = $stock_info[$result['product_id']]['quantity'];
                            //we have stock or out of stock checkout is allowed
                            if ($total_quantity > 0 || $stock_checkout) {
                                $in_stock = true;
                            }
                        }

                        $products['prod3'][] = array(
                            'product_id'     => $result['product_id'],
                            'name'           => $result['name'],
                            'blurb'          => $result['blurb'],
                            'model'          => $result['model'],
                            'rating'         => $rating,
                            'stars'          => sprintf($this->baseObject->language->get('text_stars'), $rating),
                            'thumb'          => $thumbnail,
                            'price'          => $price,
                            'raw_price'      => $result['price'],
                            'call_to_order'  => $result['call_to_order'],
                            'options'        => $options,
                            'special'        => $special,
                            'href'           =>
                                $this->baseObject->html->getSEOURL(
                                    'product/product',
                                    '&keyword='.$request['keyword'].$url.'&product_id='.$result['product_id'],
                                    '&encode'
                            ),
                            'add'            => $add,
                            'description'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                            'track_stock'    => $track_stock,
                            'in_stock'       => $in_stock,
                            'no_stock_text'  => $no_stock_text,
                            'total_quantity' => $total_quantity,
                            'tax_class_id'   => $result['tax_class_id'],
                        );
                    }
                }

                $this->baseObject->data['products'] = $products;
                if ($this->baseObject->config->get('config_customer_price')) {
                    $display_price = true;
                } elseif ($this->baseObject->customer->isLogged()) {
                    $display_price = true;
                } else {
                    $display_price = false;
                }
                $this->baseObject->data['display_price'] = $display_price;

                $url = '';
                if (isset($request['keyword'])) {
                    $url .= '&keyword='.$request['keyword'];
                }

                if (isset($request['category_id'])) {
                    $url .= '&category_id='.$request['category_id'];
                }

                if (isset($request['description'])) {
                    $url .= '&description='.$request['description'];
                }

                if (isset($request['model'])) {
                    $url .= '&model='.$request['model'];
                }

                if (isset($request['page'])) {
                    $url .= '&page='.$request['page'];
                }
                if (isset($request['limit'])) {
                    $url .= '&limit='.$request['limit'];
                }

                $sorts = array();
                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_default'),
                    'value' => 'p.sort_order-ASC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=p.sort_order&order=ASC', '&encode')
                );

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_name_asc'),
                    'value' => 'pd.name-ASC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=pd.name&order=ASC', '&encode')
                ); 

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_name_desc'),
                    'value' => 'pd.name-DESC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=pd.name&order=DESC', '&encode')
                );  

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_price_asc'),
                    'value' => 'p.price-ASC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=p.price&order=ASC', '&encode')
                ); 

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_price_desc'),
                    'value' => 'p.price-DESC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=p.price&order=DESC', '&encode')
                ); 

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_rating_desc'),
                    'value' => 'rating-DESC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=rating&order=DESC', '&encode')
                ); 

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_rating_asc'),
                    'value' => 'rating-ASC',
                    'href'  => $this->baseObject->html->getURL('product/search', $url . '&sort=rating&order=ASC', '&encode')
                );

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_date_desc'),
                    'value' => 'date_modified-DESC',
                    'href'  => $this->baseObject->html->getSEOURL('product/search', $url . '&sort=date_modified&order=DESC', '&encode')
                );

                $sorts[] = array(
                    'text'  => $this->baseObject->language->get('text_sorting_date_asc'),
                    'value' => 'date_modified-ASC',
                    'href'  => $this->baseObject->html->getSEOURL('product/search', $url . '&sort=date_modified&order=ASC', '&encode')
                );

                $this->baseObject->data['sorts'] = $sorts;

                $sort_options = array();
                foreach($sorts as $item){
                    $sort_options[$item['value']] = $item['text'];
                }

                $sorting = $this->baseObject->html->buildElement(array(
                    'type'    => 'selectbox',
                    'name'    => 'sort',
                    'options' => $sort_options,
                    'value'   => $sort.'-'.$order,
                ));
                $this->baseObject->data['sorting'] = $sorting;
                $url = '';
                if (isset($request['keyword'])) {
                    $url .= '&keyword='.$request['keyword'];
                }
                if (isset($request['category_id'])) {
                    $url .= '&category_id='.$request['category_id'];
                }

                if (isset($request['description'])) {
                    $url .= '&description='.$request['description'];
                }

                if (isset($request['model'])) {
                    $url .= '&model='.$request['model'];
                }
                $url .= '&sort='.$sort.'-'.$order;
                $url .= '&limit='.$limit;
                 $this->baseObject->data['pagination_bootstrap'] = $this->baseObject->html->buildElement(array(
                    'type'       => 'Pagination',
                    'name'       => 'pagination',
                    'text'       => $this->baseObject->language->get('text_pagination'),
                    'text_limit' => $this->baseObject->language->get('text_per_page'),
                    'total'      => $product_total,
                    'page'       => $page,
                    'limit'      => $limit,
                    'url'        => $this->baseObject->html->getURL('product/search', $url.'&page={page}', '&encode'),
                    'style'      => 'pagination',
                ));
                $this->baseObject->data['sort'] = $sort;
                $this->baseObject->data['order'] = $order;
                $this->baseObject->data['limit'] = $limit;
        $this->baseObject->data['review_status'] = $this->baseObject->config->get('enable_reviews');

        // print_r($this->baseObject->data); die;
        
        $this->baseObject->view->batchAssign($this->baseObject->data);
        $this->processTemplate('pages/product/search.tpl');

	}
}
