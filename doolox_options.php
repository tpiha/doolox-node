<?php

function doolox_settings_page() {
?>
<div class="wrap">
<h2>Doolox Plugin</h2>
<p>If you want to use Doolox as a self-hosted service, please set your Doolox installation public key here.</p>
<form method="post" action="">
<?php settings_fields( 'doolox-options' ); ?>
<?php do_settings_sections( 'doolox-options' ); ?>
<table class="form-table">
    <tbody>
    <tr valign="top">
<th scope="row"><label for="blogname">Doolox Public Key</label></th>
<!-- <td><input name="doolox_public_key" type="text" id="blogname" value="fdsafsd" class="regular-text"></td> -->
<td><textarea name="doolox_public_key" class="regular-text" rows="5" cols="50"></textarea></td>
</tr>
</tbody>
</table>
    <?php submit_button(); ?>
</form>
</div>
<?php } ?>