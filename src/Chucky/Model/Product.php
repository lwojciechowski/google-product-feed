<?php

/**
* Made with ❤ by themesfor.me
*
* XML Item generation
*/

namespace Chucky\Model;

use Chucky\Tool\XML;
use Chucky\Tool\Template;

class Product
{
	// Constraints
	const MAX_ID_LEN = 50;
	const MAX_TITLE_LEN = 150;
	const MAX_DESCRIPTION_LEN = 5000;
	const MAX_LINK_LEN = 2000;
	const MAX_PROD_TYPE_LEN = 750;

	private $product;
	private $currency;
	private $settings;
	private $category_settings;

	static $categories_cache = array();
	static $categories_meta_cache = array();

	/**
	* Setup hooks
	*
	* @param $product Product as WooCommerce object
	*/
	public function __construct(\WP_Post $product)
	{
		$this->product = new \WC_Product($product);
	}

	/**
	* Represent product as Google XML
	*
	* @return string XML representation of product ready for Google Product Feed
	*/
	public function get_xml()
	{
		$p = $this->product;

		if($this->validate_product_data($p)) {

			$template = new Template(\Chucky\__ASSETS__ . '/google-item.xml');

			$template->id = substr($p->id, 0, 50);
			$template->title = substr($p->get_title(),0,150);
			$template->description = substr($p->get_post_data()->post_excerpt,0,5000);
			$template->link = substr($p->get_permalink(),0,2000);
			$template->image_link = substr(wp_get_attachment_url($p->get_image_id()),0,2000);
			$template->condition = get_option('chucky_setting_condition','new');
			$template->availability = $p->is_in_stock() ? 'in stock' : 'out of stock';
			$template->price = sprintf('%s %s', $p->get_price(), get_woocommerce_currency());
			$template->category = htmlspecialchars(htmlspecialchars_decode($this->get_setting('category')));
			$template->product_type = get_option('chucky_setting_type','none') == 'none' ? '' : substr($this->get_type(),0,750);
			
			if($this->get_setting('identifier') !== 'unexist') {
				$template->gtin = $this->get_setting('gtin');
				$template->mpn = $this->get_setting('mpn');
				$template->brand = $this->get_setting('brand');
			} else {
				$template->identifier_exists = 'FALSE';
			}
			
			$template->weight = $this->get_weight_with_unit();

			return $template->render();
		}

		return '';
	}

	private function validate_product_data()
	{
		$p = $this->product;
		if(
			strlen($p->get_title()) == 0 ||
			strlen($p->get_post_data()->post_excerpt) == 0 ||
			strlen($p->get_permalink()) == 0 || strlen($p->get_permalink()) > self::MAX_LINK_LEN ||
			strlen(wp_get_attachment_url($p->get_image_id()))==0 || strlen(wp_get_attachment_url($p->get_image_id())) > self::MAX_LINK_LEN
		) {
			return false;
		}

		return true;
	}

	private function get_shipping_xml()
	{
		/*
		<g:shipping>
			<g:country>US</g:country>
			<g:service>Standard</g:service>
			<g:price>14.95 USD</g:price>
		</g:shipping>
		*/
	}

	private function get_type()
	{
		$categories = $this->get_categories();

		foreach($categories as $c) {
			$result[] = $c['name'];
		}

		return implode(' &gt; ', $result);
	}

	private function get_settings()
	{
		if(empty($this->settings)) {
			$this->settings = get_post_meta($this->product->id, 'chucky_settings', true);
		}

		return $this->settings;
	}

	private function get_setting($name)
	{
		// Setting from product
		$settings = $this->get_settings();

		if(isset($settings[$name])) {
			return $settings[$name];
		}

		// Setting from category
		$categories = $this->get_categories();
		
		foreach($categories as $c) {

			if(!isset(self::$categories_meta_cache[$c['term_id']])) {
				self::$categories_meta_cache[$c['term_id']] = get_metadata('woocommerce_term', $c['term_id'], 'chucky_settings', true);
			}

			$settings = self::$categories_meta_cache[$c['term_id']];

			if(!empty($settings[$name])) {
				$category_setting = $settings[$name];
			}
		}

		if(isset($category_setting)) {
			return $category_setting;
		}

		// Global setting
		$global_setting = get_option('chucky_setting_' . $name);

		if($global_setting) {
			return $global_setting;
		}

		// Null
		return '';
	}

	private function get_categories()
	{
		$args = array( 'taxonomy' => 'product_cat',);
		$terms = wp_get_post_terms($this->product->id,'product_cat', $args);

		$result = array();

		if(count($terms) == 0) {
			return '';
		}

		$digest = function ($id) use (&$result, &$digest)
		{
			if(!isset(self::$categories_cache[$id])) {
				self::$categories_cache[$id] = get_term_by('id', $id, 'product_cat', 'ARRAY_A');
			}

			$term = self::$categories_cache[$id];

			if($term['parent']) {
				$digest($term['parent']);
			}

			$result[] = $term;
		};

		$digest($terms[0]->term_id);

		return $result;
	}

	private function get_weight_with_unit()
	{	
		if(!$this->product->has_weight()) {
			return '';
		}

		$weight = $this->product->weight;
		$weight_unit = get_option('woocommerce_weight_unit');

		if('lbs' == $weight_unit) {
			$weight_unit = 'lb';
		}

		return sprintf('%s %s', $weight, $weight_unit);
	}
}

