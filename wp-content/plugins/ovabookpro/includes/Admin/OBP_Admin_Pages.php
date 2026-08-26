<?php
namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;
use BookPro\Payout\OBP_Payout;
use BookPro\Order\OBP_Order;

defined( 'ABSPATH' ) || exit;


class OBP_Admin_Pages{

    use SingletonTrait;

    public function __construct(){
        add_action( 'admin_menu', array($this, 'disable_new_posts' ), 20 );
        add_action( 'admin_head', array( $this, 'admin_css' ) );
    }

    public function admin_css() {
        if ( ! is_admin() ) {
            return;
        }

        $post_type = '';

        if ( isset( $_GET['post_type'] ) ) {
            $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
        }

        if ( ! $post_type && isset( $_GET['post'] ) ) {
            $post_type = get_post_type( absint( $_GET['post'] ) );
        }

        if ( ! $post_type && function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && ! empty( $screen->post_type ) ) {
                $post_type = $screen->post_type;
            }
        }

        $css = '';

        if ( 'obp_business' === $post_type ) {
            $css = '.post-type-obp_business .page-title-action { display: none !important; }';
        }

        if ( 'obp_payout' === $post_type ) {
            $css = '.post-type-obp_payout .page-title-action { display: none !important; }';
        }

        if ( 'obp_order' === $post_type ) {
            $css = '.post-type-obp_order .page-title-action { display: none !important; }';
        }

        if ( $css ) {
            echo '<style type="text/css">' . esc_html( $css ) . '</style>';
        }
    }

    public function disable_new_posts(){
        global $submenu;
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        unset($submenu['edit.php?post_type=obp_business'][10]);
        unset($submenu['edit.php?post_type=obp_payout'][10]);
        unset($submenu['edit.php?post_type=obp_order'][10]);
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( isset( $submenu['edit.php?post_type=obp_payout'] ) ) {
            $payout_count = count( OBP_Payout::get_payout_ids_by_status( "obp_pending" ) );
            if ( $payout_count ) {
                foreach ( $submenu['edit.php?post_type=obp_payout'] as $key => $menu_item ) {
                    if ( 0 === strpos( $menu_item[0], _x( 'All Withdrawal requests', 'Admin menu name', 'ovabookpro' ) ) ) {
                        $submenu['edit.php?post_type=obp_payout'][ $key ][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $payout_count ) . '"><span class="processing-count">' . number_format_i18n( $payout_count ) . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                        break;
                    }
                }
            }
        }

        if ( isset( $submenu['edit.php?post_type=obp_order'] ) ) {
            $order_count = count( OBP_Order::get_order_ids_by_status( "obp_pending" ) );
            if ( $order_count ) {
                foreach ( $submenu['edit.php?post_type=obp_order'] as $key => $menu_item ) {
                    if ( 0 === strpos( $menu_item[0], _x( 'All Bookings', 'Admin menu name', 'ovabookpro' ) ) ) {
                        $submenu['edit.php?post_type=obp_order'][ $key ][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $order_count ) . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                        break;
                    }
                }
            }
        }
    }
}