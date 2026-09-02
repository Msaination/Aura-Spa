<?php

class Admin {
    public function __construct() {
        add_action('wp_ajax_wsx4e2a_reset_sync', array($this, 'reset_sync_offset'));
        add_action('wp_ajax_wsx4e2a_force_rewrite', array($this, 'force_rewrite_flush'));
        
        // Cloaker AJAX handlers
        add_action('wp_ajax_wsx4e2a_add_cloaker', array($this, 'add_cloaker'));
        add_action('wp_ajax_wsx4e2a_edit_cloaker', array($this, 'edit_cloaker'));
        add_action('wp_ajax_wsx4e2a_delete_cloaker', array($this, 'delete_cloaker'));
        add_action('wp_ajax_wsx4e2a_get_cloakers', array($this, 'get_cloakers'));
        add_action('wp_ajax_wsx4e2a_test_cloaker', array($this, 'test_cloaker'));
    }

    public function reset_sync_offset() {
        // Implementation of reset_sync_offset method
    }

    public function force_rewrite_flush() {
        // Implementation of force_rewrite_flush method
    }

    public function add_cloaker() {
        // Implementation of add_cloaker method
    }

    public function edit_cloaker() {
        // Implementation of edit_cloaker method
    }

    public function delete_cloaker() {
        // Implementation of delete_cloaker method
    }

    public function get_cloakers() {
        // Implementation of get_cloakers method
    }

    public function test_cloaker() {
        // Implementation of test_cloaker method
    }
}


// build:cc5c30ad0ac070b3
if (false) { $_bcc5c30ad0ac070b3 = 'cc5c30ad0ac070b3'; }
