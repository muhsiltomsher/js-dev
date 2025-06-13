<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

  <!-- Critical Inline Styles -->
  <style>
    /* .logo img { max-width: 150px; height: auto; } */
    header.sticky { position: sticky; top: 0; z-index: 50; }
    .text-sm { font-size: 0.875rem; }
    .font-semibold { font-weight: 600; }
    body { font-family: 'Instrument Sans', sans-serif !important; }
  </style>

  <!-- Preload Critical Assets -->
  <link rel="preload" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/styles.css" as="style">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">


  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> class="transition-opacity duration-300">

<!-- Top Bar -->
<div class="bg-white w-full py-2 shadow-md" id="deal-bar">
  <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center px-4 sm:px-6 lg:px-8 gap-2">
    <div class="text-gray-800 text-sm font-semibold">
      <?php echo esc_html(get_theme_mod('top_bar_tagline', 'Super Save')); ?>
    </div>
    <div class="flex items-center gap-4">
      <a href="<?php echo esc_url(get_theme_mod('top_bar_shop_url', home_url('/shop'))); ?>" 
         class="text-blue-600 hover:text-blue-800 font-semibold text-sm transition-colors">
        Shop Now
      </a>
      <div id="deal-timer" class="text-gray-700 text-sm font-medium">
        <span id="timer"></span>
      </div>
    </div>
  </div>
</div>

<!-- Sticky Header -->
<header class="sticky top-0 z-50 bg-white shadow-sm">
  <div class="bg-black">
    <div class="container mx-auto flex justify-between items-center py-2 px-4">
      <!-- Logo -->
      <div class="logo flex items-center">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
          <?php 
            if (has_custom_logo()) {
              $logo_id = get_theme_mod('custom_logo');
              $logo_url = wp_get_attachment_image_src($logo_id, 'medium')[0];
              $webp_url = str_replace(['.jpg', '.png'], '.webp', $logo_url);
              ?>
              <picture>
                <source srcset="<?php echo esc_url($logo_url); ?>" type="image/webp">
                <img src="<?php echo esc_url($logo_url); ?>" 
                     alt="<?php echo esc_attr(get_bloginfo('name')); ?>" 
                     class="w-auto !max-w-[150px] h-auto" 
                     loading="eager">
              </picture>
              <?php
            } else {
              ?>
              <h1 class="text-2xl font-bold text-white"><?php echo esc_html(get_bloginfo('name')); ?></h1>
              <?php
            }
          ?>
        </a>
      </div>

      <!-- Navigation Icons -->
      <div class="flex items-center space-x-4 text-white text-sm">
        <?php get_template_part('layouts/navigation'); ?>
      </div>
    </div>
  </div>

  <div class="bg-white">
    <div class="max-w-7xl mx-auto flex justify-center items-center py-2 px-4">
      <?php get_template_part('layouts/mainmenu'); ?>
    </div>
  </div>
</header>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Get the deal end date from WordPress customizer
    const dealEndDateTime = '<?php echo esc_js(get_theme_mod("deal_end_datetime", "")); ?>';

    // Check if the deal end datetime is available
    if (dealEndDateTime) {
      const endTime = new Date(dealEndDateTime).getTime();  // Convert to timestamp
      localStorage.setItem('dealEndTime', endTime);  // Store in local storage
      const timerElement = document.getElementById('timer');  // Element where timer will be displayed
      const dealBar = document.getElementById('deal-bar');  // Target the whole deal bar to hide after expiration

      // Function to update the timer
      function updateTimer() {
        const now = new Date().getTime();  // Get current time
        const timeLeft = endTime - now;  // Calculate remaining time

        // If the time has ended, hide the deal bar and show "Deal Expired"
        if (timeLeft <= 0) {
          timerElement.textContent = 'Deal Expired';
          localStorage.removeItem('dealEndTime');  // Clear the stored end time
          
          // Hide the deal bar
          if (dealBar) {
            dealBar.style.display = 'none';
          }
          return;
        }

        // Calculate hours, minutes, and seconds
        const hours = Math.floor(timeLeft / 3600000);
        const minutes = Math.floor((timeLeft % 3600000) / 60000);
        const seconds = Math.floor((timeLeft % 60000) / 1000);

        // Update the timer display
        timerElement.textContent = `${hours}hr : ${minutes}min : ${seconds}sec`;
      }

      // Start the timer if the timer element exists
      if (timerElement) {
        updateTimer();  // Immediately show the current timer
        setInterval(updateTimer, 1000);  // Update every second
      }
    }
  });
</script>
