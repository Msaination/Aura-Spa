<?php
/**
 * Plugin Name: Aura Spa Treatments page styling
 * Description: Applies the live brand styling to the Amelia treatment catalog on the Treatments page.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    if (!is_page('treats') && !is_page('book-appointment') && !is_page(449)) {
        return;
    }

    wp_register_style('aura-spa-treatments-page', false, ['jacqueline-custom']);
    wp_enqueue_style('aura-spa-treatments-page');

    $css = <<<'CSS'
        body.page-id-449 .page_content_wrap,
        body.page-slug-treats .page_content_wrap,
        body.page-slug-book-appointment .page_content_wrap {
            background: #f5f2ef;
            padding-top: 0;
            padding-bottom: 64px;
        }

        body.page-id-449 .page_content_wrap > .content_wrap,
        body.page-slug-treats .page_content_wrap > .content_wrap,
        body.page-slug-book-appointment .page_content_wrap > .content_wrap {
            max-width: 1180px;
        }

        body.page-id-449 .sc_layouts_title,
        body.page-slug-treats .sc_layouts_title,
        body.page-slug-book-appointment .sc_layouts_title {
            padding: 56px 0 30px;
        }

        body.page-id-449 h1.sc_layouts_title_caption,
        body.page-slug-treats h1.sc_layouts_title_caption,
        body.page-slug-book-appointment h1.sc_layouts_title_caption {
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-weight: 300;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-size: clamp(2.6rem, 5vw, 5rem);
            line-height: 1.06;
            color: rgba(29, 41, 48, 0.88);
            text-align: center;
            margin: 0;
        }

        body.page-id-449 .sc_layouts_title .sc_layouts_title_meta,
        body.page-slug-treats .sc_layouts_title .sc_layouts_title_meta,
        body.page-slug-book-appointment .sc_layouts_title .sc_layouts_title_meta {
            color: rgba(29, 41, 48, 0.58);
            letter-spacing: 0.24em;
            text-transform: uppercase;
            font-size: 0.75rem;
            text-align: center;
            margin-top: 16px;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper {
            max-width: 100%;
            margin: 0 auto;
            background: transparent;
            border-radius: 0;
            padding: 0;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            width: 100%;
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 20px 0 0;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item {
            max-width: 100%;
            width: 100%;
            padding: 0;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-inner,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-inner {
            border: 1px solid rgba(28, 40, 47, 0.18);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 12px 28px rgba(29, 41, 48, 0.04);
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-inner:hover,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-inner:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 32px rgba(29, 41, 48, 0.06);
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-content,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-content {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            min-height: 180px;
            padding: 18px 20px 18px 18px;
            background: rgba(247, 244, 241, 0.86);
            border-left: 8px solid rgba(29, 41, 48, 0.9);
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-heading,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-heading {
            width: 100%;
            padding: 0;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-name,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-name {
            width: 100%;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-size: clamp(1.6rem, 3vw, 2.25rem);
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin: 0;
            color: #1c2d35;
            display: block;
            overflow: visible;
            text-overflow: unset;
            white-space: normal;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            margin-top: 16px;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: auto;
            color: rgba(29, 41, 48, 0.8);
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item-icon,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item-icon {
            font-size: 1.05rem;
            color: rgba(35, 64, 81, 0.9);
            opacity: 1;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item-count,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-segments__item-count {
            font-size: 1.05rem;
            line-height: 1;
            color: rgba(29, 41, 48, 0.72);
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-footer,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl__item-footer {
            position: static;
            width: 100%;
            padding: 0;
            margin-top: 20px;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn {
            width: 100%;
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid rgba(29, 41, 48, 0.45);
            background: rgba(255, 255, 255, 0.2);
            color: #1d2d35;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-size: 0.78rem;
            line-height: 1;
            box-shadow: none;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn:hover,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(29, 41, 48, 0.8);
            box-shadow: 0 10px 18px rgba(29, 41, 48, 0.08);
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn .am-button__inner,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-button.am-fcl__item-btn .am-button__inner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: inherit;
        }

        body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-icon-arrow-right,
        body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-icon-arrow-right {
            color: inherit;
            font-size: 0.9rem;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper {
            --aura-bg: #f3efe9;
            --aura-panel: #f7f1eb;
            --aura-rose: #d9b9ad;
            --aura-sand: #f0e4db;
            --aura-brown: #a09086;
            --aura-deep-brown: #7a685f;
            --aura-text: #2b2624;
            --aura-muted: #6d5b54;
            --aura-line: rgba(87, 62, 52, 0.14);
            --aura-shadow: rgba(89, 63, 54, 0.08);

            width: min(100%, 1080px);
            max-width: 1080px;
            min-height: 560px;
            margin: 0 auto 40px;
            padding: 0;
            border: 1px solid var(--aura-line);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.38);
            box-shadow: 0 18px 42px var(--aura-shadow);
            overflow: hidden;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 18px 18px 14px;
            border-bottom: 1px solid rgba(43, 38, 36, 0.12);
            background: linear-gradient(180deg, #f7f1eb 0%, #f3efe9 100%);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-wrapper {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 68px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(160, 144, 134, 0.14);
            background: rgba(255, 255, 255, 0.22);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(217, 185, 173, 0.2);
            color: var(--aura-text);
            font-size: 1rem;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-heading {
            margin: 0;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--aura-text);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-checker {
            width: 18px;
            height: 18px;
            margin-left: auto;
            border: 1px solid rgba(43, 38, 36, 0.18);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-checker-selected {
            border-color: rgba(160, 144, 134, 0.6);
            background: linear-gradient(135deg, #a09086 0%, #d9b9ad 100%);
            box-shadow: 0 8px 18px rgba(160, 144, 134, 0.18);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__support {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 12px 12px;
            color: var(--aura-muted);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__support-heading {
            margin: 0;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--aura-muted);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__support-email {
            color: var(--aura-text);
            text-decoration: none;
            font-weight: 600;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__main {
            padding: 24px 18px 18px;
            background: transparent;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__main-heading {
            margin: 0 0 18px;
            padding: 0;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__main-heading-inner-title {
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-size: clamp(1.5rem, 3vw, 2.3rem);
            font-weight: 500;
            letter-spacing: -0.03em;
            color: var(--aura-text);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__init-form {
            display: grid;
            gap: 20px;
            width: 100%;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-form-item {
            margin-bottom: 0;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__init-form__label,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-form-item__label {
            display: inline-block;
            margin-bottom: 8px;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--aura-muted);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-adv-select,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-select,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-input__wrapper,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-textarea__inner,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-input {
            width: 100%;
            border-radius: 14px;
            border: 1px solid rgba(87, 62, 52, 0.15);
            background: rgba(255, 255, 255, 0.56);
            box-shadow: none;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-input__wrapper,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-select__wrapper,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-textarea__inner {
            min-height: 52px;
            padding: 0 16px;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-input__inner,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-select__selected-item,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .el-select-dropdown__item {
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            color: var(--aura-text);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-button-continue,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-button--primary,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-package-popup-continue {
            min-height: 46px;
            padding: 0 22px;
            border: 1px solid rgba(160, 144, 134, 0.45);
            border-radius: 12px;
            background: linear-gradient(135deg, #a09086 0%, #7a685f 100%);
            color: #fff;
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-size: 0.68rem;
            box-shadow: 0 12px 20px rgba(160, 144, 134, 0.2);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-button-continue:hover,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-button--primary:hover,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-package-popup-continue:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 24px rgba(160, 144, 134, 0.22);
            background: linear-gradient(135deg, #8f7d74 0%, #6d5a52 100%);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__main-footer {
            margin-top: 20px;
            padding-top: 14px;
            display: flex;
            justify-content: flex-end;
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-slide-popup__block {
            border-radius: 18px;
            border: 1px solid var(--aura-line);
            box-shadow: 0 18px 34px var(--aura-shadow);
        }

        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__ps-popup__heading,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs__popup-x,
        body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-slide-popup__block-footer {
            font-family: "Raleway", "Helvetica Neue", Arial, sans-serif;
        }

        @media (min-width: 640px) {
            body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl,
            body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            body.page-slug-book-appointment .amelia-v2-booking #amelia-container.am-fs__wrapper .am-fs-sb__step-wrapper {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            body.page-id-449 .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl,
            body.page-slug-treats .amelia-v2-booking #amelia-container.am-fc__wrapper .am-fcl {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    CSS;

    wp_add_inline_style('aura-spa-treatments-page', $css);
}, 1500);
