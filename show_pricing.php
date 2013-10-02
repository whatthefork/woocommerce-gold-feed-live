<p class="form-field _regular_price_field">

	<label for="_regular_price">Purity</label>

<input id="_regular_price" class="wc_input_price short" type="number" min="0" step="any" placeholder="" value="

<?php 

extract($_REQUEST);

if(isset($post_id)){



global $wpdb;

$table_name = $wpdb->prefix . "postmeta";

$regularPrice=$wpdb->get_row("SELECT meta_value FROM $table_name WHERE meta_key='_regular_price' and post_id='$post_id' ", ARRAY_N);

echo $regularPrice[0];

} ?>" name="_regular_price">

</p>

<p class="form-field">

	<label for="_regular_price">Markup Amount</label>

<input id="markup" class="wc_input_price short" type="number" min="0" step="any" placeholder="" value="<?php if(isset($post_id)){

global $wpdb;

$table_name = $wpdb->prefix . "postmeta";

$markPrice=$wpdb->get_row("SELECT meta_value FROM $table_name WHERE meta_key='_sale_price' and post_id='$post_id' ", ARRAY_N);

echo $markPrice[0];

} ?>" name="_sale_price">

<br>

<span class="description">Markup Example: 20 - for $20 markup.</span>

</p>