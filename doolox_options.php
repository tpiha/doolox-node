<?php

function doolox_settings_page() {
?>
<div class="wrap">

    <h2>Doolox Plugin</h2>

    <p>If you want to use Doolox as a self-hosted service, please set your Doolox installation public key here.</p>

    <form method="post" action="options.php?_wpnonce=<?php echo wp_create_nonce(); ?>">
        <?php settings_fields( 'doolox-settings' ); ?>
        <?php do_settings_sections( 'doolox-settings' ); ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="blogname">Doolox Public Key</label></th>
                    <td><textarea id="dooloxpkg" name="dooloxpkg" class="regular-text" rows="10" cols="80"><?php echo get_option('dooloxpkg'); ?></textarea></td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>

</div>
<?php } ?>