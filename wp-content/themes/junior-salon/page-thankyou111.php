<?php
/**
 * Template Name: Thank You Page
 */
include get_template_directory() . '/layouts/header.php';




$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$order = wc_get_order($order_id);




 include get_template_directory() . '/layouts/footer.php'; ?>
