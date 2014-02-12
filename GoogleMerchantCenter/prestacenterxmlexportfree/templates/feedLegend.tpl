{*
Popis proměnných, které jsou použitelné v šabloně XML feedu. Musí korespondovat s polem PrestaCenterXmlExportFree->allowedProperties.
Description of variables/placeholders you can use in XML feed template. All variables must be defined in PrestaCenterXmlExportFree->allowedProperties array.
*}
<b>{l s='XML template' mod='prestacenterxmlexportfree'}</b><br />
<span id="prestacenterxmlexportfreeblock1"><br />
<b>{l s='General Information' mod='prestacenterxmlexportfree'}</b><br />
<br />
{l s='For the element that marks the product in the feed (e.g. SHOPITEM), mark it with the "ps_block" attribute with the "product" value (e.g. %s).' sprintf='&lt;SHOPITEM ps_block="product"&gt;' mod='prestacenterxmlexportfree'}<br />
{l s='For values of the individual elements, you can use the following variables (wildcards), including the braces:' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{shop_name}{/literal}</b> - {l s='Shop name' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{shop_url}{/literal}</b> - {l s='Shop URL' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{id}{/literal}</b> - {l s='Product ID (according to the database)' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{name}{/literal}</b> - {l s='Product name' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{manufacturer}{/literal}</b> - {l s='Manufacturer name' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{ean}{/literal}</b> - {l s='EAN13 code' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{upc}{/literal}</b> - {l s='UPS code' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{reference}{/literal}</b> - {l s='Reference' mod='prestacenterxmlexportfree'}<br />
{*<b>{literal}{supplier_reference}{/literal}</b> - {l s='Supplier reference' mod='prestacenterxmlexportfree'}<br />*}
<b>{literal}{description}{/literal}</b> - {l s='Product description' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{description_short}{/literal}</b> - {l s='Short product description.' mod='prestacenterxmlexportfree'}
<br />
<b>{literal}{categories}{/literal}</b> - {l s='List of the product categories (e.g. "Clothing | Women | Summer | Swimwear"). The separator can specify a different character, such as:' mod='prestacenterxmlexportfree'}
	<b>{literal}{categories: "&gt;"}{/literal}</b><br />
<b>{literal}{url}{/literal}</b> - {l s='Product URL (in shop)' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{img_url}{/literal}</b> - {l s='Product image URL' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{condition}{/literal}</b> - {l s='Status of the product (new, used, refurbished). You can use your own values in the feed: enter your own values after the colon, separate values with a decimal point, also, enter your own values in this order "new", "sale", "refurbished product", such as:' mod='prestacenterxmlexportfree'}
<b>{literal}{condition: "new,bazaar,bazaar"}{/literal}</b>.<br />
<b>{literal}{price_vat}{/literal}</b> - {l s='Price tax included as a decimal number. For example: "25.50"' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{price_vat_local}{/literal}</b> - {l s='Price tax included as a decimal number, including the currency sign. For example as "$25.50".' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{price_vat_iso}{/literal}</b> - {l s='Price tax included as a decimal number, including the relevant ISO code for the currency (as per ISO 4217). For example as "25.50 USD".' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{online_only}{/literal}</b> - {l s='Products is available only in the e-shop (= 1) or in the brick and mortar store (= 0).' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{update_item}{/literal}</b> - {l s='Date and time of the last product change (in GMT). The default format is Atom 1.0 (e.g. "2012-12-08T14:29:57+00:00"), but you can enter your own format (see PHP date() function). Example of the custom format:' mod='prestacenterxmlexportfree'} <b>{literal}{update_item: "Y/m/d H:i:s"}{/literal}</b><br />
<b>{literal}{update_feed}{/literal}</b> - {l s='Date and time when the feed was created (GMT). You can use your own format.' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{lang_code}{/literal}</b> - {l s='The language code of the feed (e.g. "en-us" for American English).' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{lang_code_iso}{/literal}</b> - {l s='Two-letter language code according to ISO 639-1 (e.g. "en" for English).' mod='prestacenterxmlexportfree'}<br />
<br />
<b>{l s='Note to special characters' mod='prestacenterxmlexportfree'}</b><br />
{l s='HTML tags are automatically deleted from HTML tags or special characters (angle brackets or ampersands are converted to HTML entities).' mod='prestacenterxmlexportfree'}
{l s='This behavior can be changed in the Pro version according to your preferences.' mod='prestacenterxmlexportfree'}
</span>
<br />
<u><a href="#" class="prestacenterxmlexportfreetoggle" rel="1"></a></u><br />
<span id="prestacenterxmlexportfreeblock2"><br />
<b>{l s='Product availability' mod='prestacenterxmlexportfree'}</b><br />
<br />
{l s='You have two variables at your disposal to indicate the availability of the product : "days" and "availability". Both are governed by the stock of the availability of the product (i.e. if the product is available for order), see the "Available for order" in the  Information tab (Catalog > Products). If there is a text entered to the product that is displayed in your e-shop (front office), depending on whether the product is / is not available / is available to order (see card Quantities), the values from this text may be used. The "days" variable is simpler, the first number found will be displayed.  Variable "availability" is able to distinguish between numbers (the number of days needed for delivery) and dates (e.g. when the product will be available). You can set various values for the availability. e.g. in stock / to order / sold out.' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{days}{/literal}</b> - {l s='If the product is in stock, the first number from the text displayed in your shop as products in stock or the zero will be inserted into the feed. If the product is not in stock and the product availability is empty, then no  value will be returned. Then it depends on the feed setting if the XML tag may be empty or not.' mod='prestacenterxmlexportfree'}<br />
<b>{literal}{availability}{/literal}</b> - {l s='This variable enables you to specify various custom values - like {condition}. Default functionality is this: for products that are in stock, the identical number as {days} is entered. The same applies for products that are not in stock, but can be ordered. If a product is unavailable, it will not be inserted in the feed at all.' mod='prestacenterxmlexportfree'}<br />
{l s='Customization options: enter your own values in this order - "in stock", "to order" and "sold out". They should not start with a number sign, which is reserved for special values. You can enter spaces after commas for improve readability.' mod='prestacenterxmlexportfree'}<br />
<br />
<b>{l s='Examples:' mod='prestacenterxmlexportfree'}</b><br />
- <b>{literal}{availability: "in stock, on order, sold out"}{/literal}</b> - {l s='For products that are in stock, in "stock" is inserted into the feed. The other two values are for products that are not in stock. If it is allowed to order products which are not in stock, then the second value "on order" is used. If it is not permitted to order the product and the third value is entered, the "sold out" value is used. If it is not permitted to order the product and the third value is missing, the product will not be exported at all.' mod='prestacenterxmlexportfree'}
{*{l s='Inserts the entered texts, unavailable products are added into the feed as well.' mod='prestacenterxmlexportfree'}<br />
- <b>{literal}{availability: "#, #:d.m.Y"}{/literal}</b> - {l s='For products in stock, a number of products is entered. For products in order, a number or date is entered. Unavailable products are not added to the feed at all (this is the default value).' mod='prestacenterxmlexportfree'}<br />
- <b>{literal}{availability: "3:immediately:expected product, #"}{/literal}</b> - {l s='If you want to further differentiate products in stock according to the delivery time, enter the number of days. If the value is less than or equal to the availability represented by the number of days and by the later availability value, separate it by a colon. According to this example, for the products in stock that are to be sent within three days, the "immediately" value is inserted. For the other products in stock, the "on the way\ value is inserted. For other products, number / date is entered.' mod='prestacenterxmlexportfree'}<br />
- <b>{literal}{availability: "#, #, #skipProduct"}{/literal}</b> - {l s='For products with "in stock" status, a number of products is entered. For products with "on the way" status, a number or date is entered. Unavailable products are not added to the feed at all (this is the default value).' mod='prestacenterxmlexportfree'}<br />
- <b>{literal}{availability: "3:in stock:available for order, preorder, out of stock"}{/literal}</b> - {l s='Settings for Google Merchant: for the products that are to be shipped within three days,"in stock" is inserted. For other products in stock, "available for order" is inserted. For products that are not in stock, but orders of such products are accepted, "preorder" is inserted. For the unavailable products "out of stock" is inserted.' mod='prestacenterxmlexportfree'}
*}
</span>
<br />
<u><a href="#" class="prestacenterxmlexportfreetoggle" rel="2"></a></u><br />
{* skrývání nápovědy *}
<script>
$('a.prestacenterxmlexportfreetoggle').each(function() {
	$(this).click(function(button) {
		var texty = {
			1: "{l s='Show general information' mod='prestacenterxmlexportfree'}",
			2: "{l s='Show help to product availability' mod='prestacenterxmlexportfree'}",
			3: "{l s='Show help to attributes' mod='prestacenterxmlexportfree'}"
		};
		var i = button.target.rel;
		$('#prestacenterxmlexportfreeblock'+i).fadeToggle( { 'complete' : function() {
			if ($('#prestacenterxmlexportfreeblock'+i).css('display') === 'none') {
				$(button.target).text(texty[i]);
			} else {
				$(button.target).text("{l s='Hide help' mod='prestacenterxmlexportfree'}");
			}
		}});
	});
	$(this).click();
});
</script>
