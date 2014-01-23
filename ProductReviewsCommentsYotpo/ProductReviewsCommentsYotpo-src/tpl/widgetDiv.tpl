<div id="yotpo_block_left" class="block">

  <div class="yotpo reviews" 
  	   data-appkey="{$yotpoAppkey}"
  	   data-domain="{$yotpoDomain}"
  	   data-product-id="{$yotpoProductId}"
  	   data-product-models="{$yotpoProductModel}" 
  	   data-name="{$yotpoProductName}" 
  	   data-url="{$link->getProductLink($smarty.get.id_product, $smarty.get.id_product.link_rewrite)|escape:'htmlall':'UTF-8'}" 
  	   data-image-url="{$yotpoProductImageUrl}" 
  	   data-description="{$yotpoProductDescription}" 
  	   data-bread-crumbs="{$yotpoProductBreadCrumbs}"> 
  </div>
</div>