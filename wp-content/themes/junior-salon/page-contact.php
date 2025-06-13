<?php
/**
 * Template Name: Contact Us Page
 */
include get_template_directory() . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-2 gap-10">

  <!-- LEFT: Contact Form -->
  <div>
    <h1 class="text-2xl font-bold mb-4"><?php echo apply_filters( 'wpml_translate_single_string', 'Get in Touch', 'Header Texts', 'Get in Touch' ); ?>
</h1>
    <p class="text-gray-600 mb-6"><?php echo apply_filters( 'wpml_translate_single_string', 'Have a question or need help? Our team is here to assist you.', 'Header Texts', 'Have a question or need help? Our team is here to assist you.' ); ?>
</p>
    <?php echo do_shortcode('[wpforms id="553"]'); ?>
  </div>

  <!-- RIGHT: FAQs & Contact Info -->
  <div>
    <!-- FAQ Accordion -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4"><?php echo apply_filters( 'wpml_translate_single_string', 'Frequently Asked Questions', 'Header Texts', 'Frequently Asked Questions' ); ?>
</h2>
      <div class="space-y-2">
       


 <?php echo do_shortcode('[sp_easyaccordion id="567"]'); ?>
      </div>
      <a href="<?php echo home_url('/faq'); ?>" class="inline-block mt-4 text-blue-600 underline text-sm font-medium">Have more questions? Visit our full FAQ</a>
    </div>

    <!-- Contact Info -->
   


    <!-- Contact Info -->
<div class="bg-gray-50 p-6 rounded-lg shadow">
  <h2 class="text-xl font-semibold mb-4"><?php echo apply_filters( 'wpml_translate_single_string', 'Contact Us', 'Header Texts', 'Contact Us' ); ?>
</h2>

  <?php
    $customer_care = get_field('customer_care');
    $whatsapp      = get_field('whatsapp');
    $phone         = get_field('phone');
    $email         = get_field('email');
  ?>

  <?php if ($customer_care): ?>
    <p class="text-sm text-gray-700 mb-2">
      <strong><?php echo apply_filters( 'wpml_translate_single_string', 'Customer Care', 'Header Texts', 'Customer Care' ); ?>
:</strong> <?= esc_html($customer_care); ?>
    </p>
  <?php endif; ?>

  <?php if ($whatsapp): ?>
    <p class="text-sm text-gray-700 mb-2">
      <strong><?php echo apply_filters( 'wpml_translate_single_string', 'WhatsApp', 'Header Texts', 'WhatsApp' ); ?>
:</strong>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp); ?>" class="text-blue-600"><?= esc_html($whatsapp); ?></a>
    </p>
  <?php endif; ?>

  <?php if ($phone): ?>
    <p class="text-sm text-gray-700 mb-2">
      <strong><?php echo apply_filters( 'wpml_translate_single_string', 'Phone', 'Header Texts', 'Phone' ); ?>
:</strong> <span class="font-medium"><?= esc_html($phone); ?></span>
    </p>
  <?php endif; ?>

  <?php if ($email): ?>
    <p class="text-sm text-gray-700 mb-2">
      <strong><?php echo apply_filters( 'wpml_translate_single_string', 'Email', 'Header Texts', 'Email' ); ?>
:</strong>
      <a href="mailto:<?= esc_attr($email); ?>" class="text-blue-600"><?= esc_html($email); ?></a>
    </p>
  <?php endif; ?>

    <div class="flex items-center gap-4">
      <span class="text-white text-sm font-semibold"><?php echo apply_filters( 'wpml_translate_single_string', 'STAY IN TOUCH', 'Header Texts', 'STAY IN TOUCH' ); ?>
 </span>
      <div class="flex gap-4 text-2xl text-gray-300">
        <?php
          $social_links = [
            'facebook_url' => ['icon' => 'fab fa-facebook', 'hover' => 'hover:text-blue-600'],
            'instagram_url' => ['icon' => 'fab fa-instagram', 'hover' => 'hover:text-pink-500'],
            'youtube_url' => ['icon' => 'fab fa-youtube', 'hover' => 'hover:text-red-600'],
          ];
          foreach ($social_links as $key => $val) {
            $url = get_theme_mod($key);
            if ($url) {
              echo '<a href="'. esc_url($url) .'" class="'. esc_attr($val['hover']) .' transition-all transform hover:scale-110" target="_blank"><i class="'. esc_attr($val['icon']) .'"></i></a>';
            }
          }
        ?>
      </div> </div>
 </div>
</div>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
