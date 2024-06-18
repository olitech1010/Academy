<style>
	.sbundle-items::before{
		display: none !important;
		visibility: hidden !important;
	}
</style>
<?php foreach ($my_bundles->result_array() as $key => $my_bundle):
	$user_id = $this->session->userdata('user_id');
	$course_ids = json_decode($my_bundle['course_ids']);?>

		<div class="col-lg-6 mb-4">
            <div class="sbundle-items">
                <div class="bundle-head d-flex justify-content-between align-items-center flex-wrap">
                    <a href="<?php echo site_url('bundle_details/'.$my_bundle['id'].'/'.slugify($my_bundle['title'])); ?>">
                        <div class="title d-flex align-items-center g-12">
                            <h4 class="name"><?php echo $my_bundle['title'];?></h4>
                            <p class="info"><?php echo count($course_ids).' '.site_phrase('courses'); ?></p>
                        </div>
                    </a>
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
                            <a href="<?php echo site_url('addons/course_bundles/lesson/'.rawurlencode(slugify($course_details['title'])).'/'.$my_bundle['id'].'/'.$course_details['id']); ?>" target="_blank">
                            <div class="content">
                                <div class="img"><img src="<?php echo $this->crud_model->get_course_thumbnail_url($course_details['id']); ?>" alt="" /></div>
                                <h3 class="title"><?php echo $course_details['title']; ?></h3>
                                <p><i class="fas fa-play-circle"></i></p>
                            </div>
                            </a>
                        </div>
                    </li>
                    <?php endif;?>
                    <?php endforeach;?>
                </ul>
                </div>
                <div class="d-flex justify-content-between">
                    <?php if(get_bundle_validity($my_bundle['id']) == 'valid'): ?>
                        <a href="javascript:void(0);" class="bundle-foot w-100 me-3" onclick="showAjaxModal('<?php echo site_url('addons/course_bundles/bundle_rating/'.$my_bundle['id']); ?>')"><?php echo get_phrase('Rating')?> </a>
                    <?php else: ?>
                        <a href="<?php echo site_url('course_bundles/buy/'.$my_bundle['id']); ?>" class="bundle-foot w-100 me-3" onclick="bundle_rating('<?php echo $my_bundle['id']; ?>')"><?php echo get_phrase('renew_subscription')?> </a>
                    <?php endif; ?>
                    <a href="javascript:void(0);" onclick="showAjaxModal('<?php echo base_url('/addons/course_bundles/bundle_purchase_history/'.$my_bundle['id'])?>', '','xl')" class="bundle-foot w-100"><?php echo get_phrase('Purchase History')?> </a>
                </div>
            </div>
        </div>
<?php endforeach; ?>
<?php include 'course_bundle_scripts.php'; ?>