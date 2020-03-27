<?php

namespace Windzfare\Admin\Menu;
/**
 * Dashboard main template
 */

defined( 'ABSPATH' ) || die();

use Windzfare\Helpers\Utils as Utils;
if(isset($_POST['windzfare-admin-action-submit'])){
    Utils::update_option('windzfare_options', 'windzfare_featured_campaign', $_POST['windzfare_featured_campaign']);
}
$data =  Utils::get_option( 'windzfare_featured_campaign', 'windzfare_options');
?>

<div class="windzfare-wrapper">
    <div class="windzfare-dashboard-panel">
        <form action="" method="POST" id="windzfare-admin-action" enctype="multipart/form-data">
            <select name = "windzfare_featured_campaign" >
            <?php foreach(Utils::get_causes_list() as $key=>$value): ?>
                <option value = "<?php echo $key; ?>" <?php echo ($key == $data ) ? "selected" : ""; ?>><?php echo $value; ?></option>
            <?php endforeach; ?>
            </select>
            <button name = "windzfare-admin-action-submit" class="windzfare-admin-action-submit"><?php esc_html_e('Save Changes', 'windzfare'); ?></button>
        </form>
    </div>
</div>