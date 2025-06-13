<?php

/**
 * Handles free plugin user dashboard
 * 
 * @package SCFW_Size_Chart_For_Woocommerce
 * @since   2.4.0
 */
// Exit if accessed directly
if ( !defined( 'WPINC' ) ) {
    exit;
}
$file_dir_path = 'header/plugin-header.php';
if ( file_exists( plugin_dir_path( __FILE__ ) . $file_dir_path ) ) {
    require_once plugin_dir_path( __FILE__ ) . $file_dir_path;
}
// Function for free plugin content
function scfw_free_import_export_settings_content() {
    ?>
    <div class="thedotstore-main-table res-cl scfw-import-export-table scfw-upgrade-pro-to-unlock">
        <h2><?php 
    esc_html_e( 'Import & Export Settings', 'size-chart-for-woocommerce' );
    ?><div class="scfw-pro-label"></h2>
        <table class="table-outer import-export-table">
            <tbody>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="blogname"><strong><?php 
    echo esc_html__( 'Export Size Chart Rule', 'size-chart-for-woocommerce' );
    ?></strong></label>
                    </th>
                    <td>
                        <div class="whsm_main_container export_settings_container">
                            <p class="scfw_button_container">
                                <input type="button" name="scfw_export_settings_pro" id="scfw_export_settings" class="button button-primary" value="<?php 
    esc_attr_e( 'Export', 'size-chart-for-woocommerce' );
    ?>" />
                            </p>
                            <p class="scfw_content_container">
                                <input type="hidden" name="scfw_export_action_pro" value="scfw_export_settings_action_pro"/>
                                <span><?php 
    esc_html_e( 'Export the size chart rules settings for this site as a .json file. This allows you to easily import the configuration into another site. Please make sure simple product and variation products slugs must be unique.', 'size-chart-for-woocommerce' );
    ?></span>
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc"><label
                            for="blogname"><strong><?php 
    echo esc_html__( 'Import Size Chart Rule', 'size-chart-for-woocommerce' );
    ?></strong></label>
                    </th>
                    <td>
                        <div class="scfw_main_container import_settings_container">
                            <p class="scfw_file_container">
                                <input type="file" name="import_file_pro" accept="application/json" />
                            </p>
                            <p class="scfw_button_container">
                                <input type="button" name="scfw_import_settings_pro" id="scfw_import_settings_pro" class="button button-primary" value="<?php 
    esc_attr_e( 'Import', 'size-chart-for-woocommerce' );
    ?>" />
                            </p>
                            <p class="scfw_content_container">
                                <span><?php 
    esc_html_e( 'Import the size chart rules settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'size-chart-for-woocommerce' );
    ?></span>
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php 
}

scfw_free_import_export_settings_content();
?>
</div>
</div>
</div>
</div>
