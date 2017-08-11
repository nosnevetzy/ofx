{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='product_miniature_item'}
  <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
    <div class="thumbnail-container">
      {block name='product_thumbnail'}
        <a href="{$product.url}" class="thumbnail product-thumbnail">
          <img
            src = "{$product.cover.bySize.home_default.url}"
            alt = "{$product.cover.legend}"
            data-full-size-image-url = "{$product.cover.large.url}"
          >
        </a>
      {/block}

      <div class="product-description">
        {block name='product_name'}
          <h1 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name|truncate:30:'...'}</a></h1>
        {/block}

        {block name='product_price_and_shipping'}
          {if $product.show_price}
            <div class="product-price-and-shipping">
              {if $product.has_discount}
                {hook h='displayProductPriceBlock' product=$product type="old_price"}

                <span class="regular-price">{$product.regular_price}</span>
                {if $product.discount_type === 'percentage'}
                  <span class="discount-percentage">{$product.discount_percentage}</span>
                {/if}
              {/if}

              {hook h='displayProductPriceBlock' product=$product type="before_price"}
              <div class="product-prices">
              <span itemprop="price" class="price">{$product.price}</span>
              </div>
              {hook h='displayProductPriceBlock' product=$product type='unit_price'}

            {hook h='displayProductPriceBlock' product=$product type='weight'}
            <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
              <input type="hidden" name="token" value="{$static_token}">
              <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
              <input type="hidden" name="id_customization" value="0" id="product_customization_id">
              {block name='product_variants'}
                {include file='catalog/_partials/custom-product-variants.tpl'}
              {/block}
              <div class="homepage-custom-product-options">
                <input type="number" class="input-group form-control quantity" name="qty" min="1" value="1">
                <button data-button-action="add-to-cart" class="btn btn-primary add-to-cart" type="submit">
                  Add to cart
                </button>
              </div>
              {block name='product_refresh'}
                <input class="product-refresh-home ps-hidden-by-js" type="submit" value="{l s='Refresh' d='Shop.Theme.Actions'}" style="display: none;">
              {/block}
            </form> 

          </div>
        {/if}
      {/block}

      {block name='product_reviews'}
        {hook h='displayProductListReviews' product=$product}
      {/block}
    </div>

    {block name='product_flags'}
      <ul class="product-flags">
        {foreach from=$product.flags item=flag}
          <li class="{$flag.type}">{$flag.label}</li>
        {/foreach}
      </ul>
    {/block}

    <div class="highlighted-informations{if !$product.main_variants} no-variants{/if} hidden-sm-down">
      {block name='quick_view'}
        <a class="quick-view" href="#" data-link-action="quickview">
          <i class="material-icons search">&#xE8B6;</i> {l s='Quick view' d='Shop.Theme.Actions'}
        </a>
      {/block}

      {block name='product_variants'}
        {if $product.main_variants}
          {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
        {/if}
      {/block}
    </div>

  </article>
{/block}
