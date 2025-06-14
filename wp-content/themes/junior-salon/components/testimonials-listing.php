<section class="pt-[30px] pb-[50px]">
 <h2 class="text-center text-[15px] uppercase font-normal tracking-widest mb-1">
  <?php echo apply_filters('wpml_translate_single_string', 'Testimonials', 'junior-salon', 'Testimonials'); ?>
</h2>
<h3 class="text-center text-lg sm:text-3xl font-bold uppercase mb-10 mt-0">
  <?php echo apply_filters('wpml_translate_single_string', 'Customer Experiences', 'junior-salon', 'Customer Experiences'); ?>
</h3>

  <div class="mx-auto">
    <div id="testimonial-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[20px]">
      <?php
      $query = new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => 3,
        'paged' => 1
      ]);

      if ($query->have_posts()):
        while ($query->have_posts()): $query->the_post();
          $rating = get_field('rating');
          ?>
          <div class="flex flex-col h-full">
            <?php if (has_post_thumbnail()): ?>
              <img src="<?php the_post_thumbnail_url('large'); ?>" class="w-full object-cover h-[300px]" alt="<?php the_title_attribute(); ?>">
            <?php endif; ?>

            <div class="bg-[#FDF3CE] text-center p-5 flex flex-col justify-between flex-1">
              <h4 class="text-sm font-bold mb-2"><?php the_title(); ?></h4>
              <div class="text-yellow-500 text-lg mb-2">
                <?php for ($i = 0; $i < 5; $i++): ?>
                  <span><?php echo $i < $rating ? '★' : '☆'; ?></span>
                <?php endfor; ?>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed"><?php the_content(); ?></p>
            </div>
          </div>
          <?php
        endwhile;
      endif;
      wp_reset_postdata();
      ?>
    </div>

  <!--  <div class="text-center mt-8">
      <a href="/testimonials" class="text-sm font-semibold underline underline-offset-4 text-black hover:text-black transition">
        VIEW ALL
      </a>
    </div> -->
  </div>
</section>

<script>
let page = 2;
const loadMoreBtn = document.getElementById('load-more-testimonials');
const container = document.getElementById('testimonial-container');

function loadTestimonials() {
  fetch(`<?php echo admin_url('admin-ajax.php'); ?>?action=load_testimonials&page=${page}`)
    .then(res => res.text())
    .then(html => {
      if (html.trim() === '') {
        loadMoreBtn?.remove();
      } else {
        container.insertAdjacentHTML('beforeend', html);
        page++;
      }
    });
}

loadMoreBtn?.addEventListener('click', loadTestimonials);
</script>
