<?php
// Access any required data or variables
// Example: $group_id = get_post_meta($group_id, 'my_custom_data', true);
// Get the path to the plugin directory
$plugin_dir = plugin_dir_path(__FILE__);

// Require the file using the plugin directory path
require_once $plugin_dir . '../ld-helper.php';

$object = new Subgroup_Course_Management_Helper_Function();
?>

<div id="tab-3" class="tab-content">
    <div class="ldgr-custom-tab-content">
        <?php
        $current_user = get_current_user_id();
        $group_id =  $object->get_the_group_id($current_user);

        if (learndash_is_group_leader_user($current_user)) {
        ?>
            <div id="table-top">
                <div><?php echo $object->selectCourse($group_id) ?></div>
                <div><?php echo $object->selectEntries($group_id) ?></div>
            </div>
            
            <div id="report_container"><div>
    <?php } ?>
    </div>
</div>