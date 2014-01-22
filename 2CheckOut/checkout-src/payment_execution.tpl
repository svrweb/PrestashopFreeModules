
{capture name=path}{l s='Credit Card/PayPal' mod='checkout'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='checkout'}</h2>

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='You have chosen to pay by Credit/Debit card - 2Checkout Online validation.' mod='checkout'}</h3>

<form name="checkout_confirmation" action="{$CheckoutUrl}" method="post" />
    <input type="hidden" name="lang" value="{$lang_iso}">
    <input type="hidden" name="sid" value="{$sid}" />
    <input type="hidden" name="merchant_order_id" value="{$cart_order_id}" />
    <input type="hidden" name="card_holder_name" value="{$card_holder_name}" />
    <input type="hidden" name="street_address" value="{$street_address}" />
    <input type="hidden" name="street_address2" value="{$street_address2}" />
    <input type="hidden" name="city" value="{$city}" />
    <input type="hidden" name="state" value="{if $state}{$state->name}{else}{$outside_state}{/if}" />
    <input type="hidden" name="zip" value="{$zip}" />
    <input type="hidden" name="country" value="{$country}" />
    <input type="hidden" name="ship_name" value="{$ship_name}" />
    <input type="hidden" name="ship_street_address" value="{$ship_street_address}" />
    <input type="hidden" name="ship_street_address2" value="{$ship_street_address2}" />
    <input type="hidden" name="ship_city" value="{$ship_city}" />
    <input type="hidden" name="ship_state" value="{if $ship_state}{$ship_state->name}{else}{$outside_state}{/if}" />
    <input type="hidden" name="ship_zip" value="{$ship_zip}" />
    <input type="hidden" name="ship_country" value="{$ship_country}" />
    {if sprintf("%01.2f", $check_total) == sprintf("%01.2f", $total)}
        {counter assign=i}
        {foreach from=$products item=product}
        <input type="hidden" name="mode" value="2CO" />
        <input type="hidden" name="li_{$i}_product_id" value="{$product.id_product}" />
        <input type="hidden" name="li_{$i}_quantity" value="{$product.quantity}" />
        <input type="hidden" name="li_{$i}_name" value="{$product.name}" />
        <input type="hidden" name="li_{$i}_description" value="{$product.description_short}" />
        <input type="hidden" name="li_{$i}_price" value="{$product.price}" />
        {counter print=false}
        {/foreach}
        {if isset($shipping_cost)}
            {counter assign=i}
            <input type="hidden" name="li_{$i}_type" value="shipping" />
            <input type="hidden" name="li_{$i}_name" value="{$carrier}" />
            <input type="hidden" name="li_{$i}_price" value="{$shipping_cost}" />
        {/if}
        {if isset($tax)}
            {counter assign=i}
            <input type="hidden" name="li_{$i}_type" value="tax" />
            <input type="hidden" name="li_{$i}_name" value="Tax" />
            <input type="hidden" name="li_{$i}_price" value="{$tax}" />
        {/if}
        {if isset($discount)}
            {counter assign=i}
            <input type="hidden" name="li_{$i}_type" value="coupon" />
            <input type="hidden" name="li_{$i}_name" value="Discounts" />
            <input type="hidden" name="li_{$i}_price" value="{$discount}" />
        {/if}
    {else}
        {counter assign=i}
        {foreach from=$products item=product}
        <input type="hidden" name="id_type" value="1" />
        <input type="hidden" name="c_prod_{$i}" value="{$product.id_product},{$product.quantity}" />
        <input type="hidden" name="c_name_{$i}" value="{$product.name}" />
        <input type="hidden" name="c_description_{$i}" value="{$product.description_short}" />
        <input type="hidden" name="c_price_{$i}" value="{$product.price}" />
        {counter print=false}
        {/foreach}
    <input type="hidden" name="cart_order_id" value="{$cart_order_id}" />
    <input type="hidden" name="total" value="{$check_total}" />
    {/if}

    <p>&nbsp;</p>
    <p>
		{l s='Here is a short summary of your order:' mod='checkout'}
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='checkout'}
			<span id="amount_{$currency->id}" class="price">{convertPriceWithCurrency price=$total currency=$currency}</span>
			{if $use_taxes == 1}
			{l s='(tax incl.)' mod='checkout'}
			{/if}
	</p>

    <input type="hidden" name="email" value="{$email}" />
    <input type="hidden" name="phone" value="{$phone}" />
    <input type="hidden" name="currency_code" value="{$currency_code}" />
    <input type="hidden" name="secure_key" value="{$secure_key}" />
    <input type="hidden" name="x_receipt_link_url" value="{$x_receipt_link_url}" />

    <p>
        {l s='You will be redirected to 2Checkout to complete your payment.' mod='checkout'}
        <br /><br />
        <b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='checkout'}.</b>
    </p>
    <p class="cart_navigation">
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='checkout'}" class="exclusive_large" />
        <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='checkout'}</a>
    </p>

</form>

{/if}
