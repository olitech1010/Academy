<!---------- Bread Crumb Area Start ---------->
<?php include "breadcrumb.php"; ?>
<!---------- Bread Crumb Area End ---------->

<!-- Start Tutor list -->
<section class="pt-50 pb-120">
    <div class="container">
    <!-- Search Results & Input -->
    <div class="d-flex justify-content-between pb-50">
        <p class="searchResult">
            <?php if(isset($search_string)): ?>
                <span><?php echo site_phrase('found_number_of_bundles'); ?> : <?php echo count($course_bundles->result_array()); ?></span>
            <?php else: ?>
                <span><?php echo site_phrase('showing_on_this_page'); ?> : <?php echo count($course_bundles->result_array()); ?></span>
            <?php endif; ?>
        </p>
        <form action="<?php site_url('course_bundles/search/query'); ?>" method="get">
            <div class="s_search">
                <input type="text" class="form-control" name="string" value="<?php if(isset($search_string)) echo $search_string; ?>" placeholder="<?php echo site_phrase('search_for_bundle'); ?>"/>
                <span><img src="<?php echo base_url('assets/frontend/default-new/image/icon/s_search.svg')?>" alt="" /></span>
            </div>
        </form>
    </div>
    <!-- Items -->
    <div class="row">
        <?php foreach($course_bundles->result_array() as $bundle):
            $instructor_details = $this->user_model->get_all_user($bundle['user_id'])->row_array();
            $course_ids = json_decode($bundle['course_ids']);
            sort($course_ids);
        ?>
        <div class="col-lg-6 mb-4">
            <div class="sbundle-items">
                <div class="bundle-head d-flex justify-content-between align-items-center flex-wrap">
                    <a href="<?php echo site_url('bundle_details/'.$bundle['id'].'/'.slugify($bundle['title'])); ?>">
                        <div class="title d-flex align-items-center g-12">
                            <h4 class="name"><?php echo $bundle['title'];?></h4>
                            <p class="info"><?php echo count($course_ids).' '.site_phrase('courses'); ?></p>
                        </div>
                    </a>
                <p class="price"><?php echo currency($bundle['price']); ?></p>
                </div>
                <div class="bundle-body">
                <ul>
                <?php $total_courses_price = 0; ?>
                <?php foreach($course_ids as $key => $course_id):
                    ++$key;
                    $this->db->where('id', $course_id);
                    $this->db->where('status', 'active');
                    $course_details = $this->db->get('course')->row_array();

                    if ($course_details['is_free_course'] != 1):
                        if ($course_details['discount_flag'] != 1)
                            $total_courses_price += $course_details['price'];
                        else{
                            $total_courses_price += $course_details['discounted_price'];
                        }
                    endif;
                    if($key <= count($course_ids)): ?>
                    <li>
                        <div class="sbundle-item">
                            <a href="<?php echo site_url('home/course/'.rawurlencode(slugify($course_details['title'])).'/'.$course_details['id']); ?>" target="_blank">
                            <div class="content">
                                <div class="img"><img src="<?php echo $this->crud_model->get_course_thumbnail_url($course_details['id']); ?>" alt="" /></div>
                                <h3 class="title"><?php echo $course_details['title']; ?></h3>
                            </div>
                            </a>
                            <div class="price"><?php echo currency($course_details['price']); ?></div>
                        </div>
                    </li>
                    <?php endif;?>
                    <?php endforeach;?>
                </ul>
                </div>
                <?php $is_purchase = $this->db->where('user_id', $this->session->userdata('user_id'))->where('bundle_id', $bundle['id'])->get('bundle_payment')->num_rows();?>
                <?php if($is_purchase > 0):?>
                    <a href="<?php echo base_url('home/my_bundles')?>" class="bundle-foot"><?php echo get_phrase('My Bundles')?></a>
                <?php else:?>
                    <a href="<?php echo base_url('course_bundles/buy/'.$bundle['id'])?>" class="bundle-foot"><?php echo get_phrase('Purchase for')?> <?php echo currency($bundle['price']); ?></a>
                <?php endif?>
            </div>
        </div>
        <?php endforeach;?>
        <div class="col-md-12 text-center">
            <?php if($course_bundles->num_rows() <= 0):
                echo site_phrase('no_result_found').' !';
            endif; ?>
        </div>
        <nav>
            <?php echo $this->pagination->create_links(); ?>
        </nav>
       
    </div>
    </div>
</section>
<!-- End Tutor list -->

<?php include "course_bundle_scripts.php"; ?>