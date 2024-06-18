<?php
	if(file_exists('uploads/course_bundle/banner/'.$bundle_details['banner'])):
		$bundle_banner = base_url('uploads/course_bundle/banner/'.$bundle_details['banner']);
	else:
		$bundle_banner = base_url('uploads/course_bundle/banner/thumbnail.png');
	endif;

	//Bundle Rating
	$ratings = $this->course_bundle_model->get_bundle_wise_ratings($bundle_details['id']);
	$bundle_total_rating = $this->course_bundle_model->sum_of_bundle_rating($bundle_details['id']);
	if ($ratings->num_rows() > 0) {
		$bundle_average_ceil_rating = ceil($bundle_total_rating / $ratings->num_rows());
	}else {
		$bundle_average_ceil_rating = 0;
	}
?>

<style>
		
	/*------ Corese -------- */

	.corses{
		background-color: var(--bg-white-2);
		padding-bottom: 80px;
		margin-bottom: 0 !important;
		
	}
	.corses h1 {
	padding-top: 60px;
		font-size: 32px;
		font-weight: 700;
		color: var(--color-1);
		margin-bottom: 30px;
		
	}
	.corses h1 span{
		position: relative;
	}

	.corses h1 span::before{
		content: "";
		background-image: url("../image/h-1-bn-shape-2.png");
		width: 116px;
		height: 21px;
		position: absolute;
		background-size: 119px;
		top: 31px;
		left: 89px;
	}
	.corses p{
		margin-bottom: 50px;
	}
	.corses .corses-card .corses-card-body img{
		width: 100%;
		height: 100%;
		border-radius: 10px 10px 0px 0px;
	}
	.corses-card .corses-card-body .corses-text h5{
		font-size: 16px;
		font-weight: 600;
		padding-right: 7px;
		transition: .5s;
		color: var(--color-1);
		/* color: #1E293B;   */
	}
	.corses-card-body:hover .corses-text h5{
	color: #754FFE;
	}
	.corses-card .corses-card-body .corses-text{
		padding: 12px;
	}
	.corses-card .corses-card-body .corses-text p{
		margin-bottom: 16px;
		font-size: 12px;
		color: var(--color-1);
	}
	.corses-card .corses-card-body .corses-text p a{
	text-decoration: none;
	color: #676C7D;
	}
	.corses-card .corses-card-body .corses-text .review-icon{
		display: flex;
	}
	.corses-card .corses-card-body .corses-text .review-icon i{
		color: #F9B23A;
		font-size: 12px;
		padding: 2px;
	
	}
	.corses-list-view-card-body .corses-text .review-icon i{
	margin: 0 5px;
	}
	.corses-card .corses-card-body .corses-text .review-icon p{
		padding-left: 5px;
		
	}
	.corses-card-body {
		background-color: var(--bg-white);
		border-radius: 10px;
		margin-bottom: 20px;
		transition: .2s;
		box-sizing: border-box;
		box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
		overflow: hidden;
	}

	/* .corses-card-body:hover {
		-ms-transform: scale(1.1); 
		-webkit-transform: scale(1.1); 
		transform: scale(1.1); 
	} */
	.corses-card-image{
	overflow: hidden;
	transition: .5s;
	}
	.corses-card-body:hover .corses-card-image img{
	transform: scale(1.1);
	transition: .5s;
	}

	.corses-card .corses-card-body .corses-text .corses-price-border{
		border-top: 1.5px solid #676c7d3a;
	}
	.corses-card .corses-card-body .corses-text .corses-price{
		margin-top: 10px;
		display: flex;
	}
	.corses-price-left{
		display: flex;
	}
	.corses-price-left span{
		color: #63CC94;
	}
	.corses-card-body .corses-text .corses-price .corses-price-right{
		display: flex;
		justify-content: flex-end;
	}
	.corses-card-body .corses-text .corses-price{
		justify-content: space-between;
	}
	.corses-card-body .corses-text .corses-price .corses-price-right i{
		color: #754FFE;
		padding-right: 5px;
	}
	.corses-card .corses-card-body .corses-card-image .corses-card-image-text h3{
		font-size: 12px;
		font-weight: 600;
	}
	.corses-card .corses-card-body .corses-card-image{
		position: relative;
		
	}
	.corses-card .corses-card-body .corses-card-image .corses-card-image-text h3{
		color: #F25C88;
	}
	.corses-card .corses-card-body .corses-card-image .corses-card-image-text{
		position: absolute;
		background-color: #FDE6EC;
		padding: 8px 13px;
		bottom: 10px;
		right: 0;
	}
	.corses-card .corses-card-body .corses-card-image .corses-card-image-text::after{
		clip-path: polygon(100% 0, 100% 50%, 100% 100%, 0 100%, 95% 50%, 0% 0%);
		background-color: #FDE6EC;
		width: 20px;
		content: "";
		position: absolute;
		height: 100%;
		left: -19px;
		top: 0;
	}
	.corses-price p {
		margin-bottom: 0 !important;
	}
	.corses-card .corses-card-body .corses-card-image .text-2{
		background-color: #DAE7FF;
	}
	.corses-card .corses-card-body .corses-card-image .text-2 h3{
		color: #0E63FF;
	}
	.corses-card .corses-card-body .corses-card-image .text-2::after{
		clip-path: polygon(100% 0, 100% 50%, 100% 100%, 0 100%, 95% 50%, 0% 0%);
		background-color: #DAE7FF;
		width: 20px;
		content: "";
		position: absolute;
		height: 100%;
		left: -19px;
		top: 0;
	}
	.corses-card .corses-card-body .corses-card-image .text-3 h3{
		color: #0ECE96;
	}
	.corses-card .corses-card-body .corses-card-image .text-3 {
		background-color: #DAF7EF;
	}
	.corses-card .corses-card-body .corses-card-image .text-3::after{
		clip-path: polygon(100% 0, 100% 50%, 100% 100%, 0 100%, 95% 50%, 0% 0%);
		background-color: #DAF7EF;
		width: 20px;
		content: "";
		position: absolute;
		height: 100%;
		left: -19px;
		top: 0;
	}
	.corses-card-image .red-heart i {
		color: #FF3434;
	}
	.corses-card-image .corses-icon i {
		color: #6e798a81;
		background-color: #fff;
		border-radius: 50%;
		padding: 8px;
		position: absolute;
		top: 14px;
		right: 7px;
		font-size: 14px;
	}
	.star0{
		color:#676C7D !important;
	}
</style>

<!---------- Bread Crumb Area Start ---------->
<?php include "breadcrumb.php"; ?>
<!---------- Bread Crumb Area End ---------->

<!-- Start Instructor -->
<section class="pb-120 pt-50">
	<div class="container">
	<h4 class="s_title_one pb-30"><?php echo get_phrase('Included Courses')?></h4>
	<div class="corses pb-0 bg-transparent">
		<div class="corses-card pb-50">
			<div class="row">
				<?php foreach(json_decode($bundle_details['course_ids']) as $key => $course_id):
				$this->db->where('id', $course_id);
				$this->db->where('status', 'active');
				$course = $this->db->get('course')->row_array();
					
				$instructor_details = $this->user_model->get_all_user($course['user_id'])->row_array();
				$course_duration = $this->crud_model->get_total_duration_of_lesson_by_course_id($course['id']);
				$lessons = $this->crud_model->get_lessons('course', $course['id']);
				if($course == null) continue;
				?>
				<div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
					<div class="corses-card-body">
						<div class="corses-card-image">
						<a href="<?php echo site_url('home/course/' . rawurlencode(slugify($course['title'])) . '/' . $course['id']); ?>"> <img src="<?php echo $this->crud_model->get_course_thumbnail_url($course['id']); ?>" width="100%" /></a>
						<div class="corses-icon red-heart">
							<i class="fa-solid fa-heart"></i>
						</div>
						<div class="corses-card-image-text">
							<h3><?php echo site_phrase($course['level']); ?></h3>
						</div>
						</div>
						<div class="corses-text">
						<h5><a href="<?php echo site_url('home/course/' . rawurlencode(slugify($course['title'])) . '/' . $course['id']); ?>"> <?php echo $course['title'];?></a></h5>
						<div class="review-icon align-items-center mt-2">
							<?php
								$total_rating =  $this->crud_model->get_ratings('course', $course['id'], true)->row()->rating;
								$number_of_ratings = $this->crud_model->get_ratings('course', $course['id'])->num_rows();
								if ($number_of_ratings > 0) {
									$average_ceil_rating = ceil($total_rating / $number_of_ratings);
								} else {
									$average_ceil_rating = 0;
								}
								for ($i = 1; $i < 6; $i++) : ?>
									<?php if ($i <= $average_ceil_rating) : ?>
										<i class="fa-solid fa-star"></i>
									<?php else : ?>
										<i class="fa-solid fa-star star0"></i>
									<?php endif; ?>
							<?php endfor; ?>
							<p class="mb-0">(<?php echo $number_of_ratings.' '.site_phrase('reviews'); ?>)</p>
						</div>
						<p><?php echo substr_replace($course['description'], " ...", 80)?></p>
						<div class="corses-price-border">
							<div class="corses-price">
							<div class="corses-price-left">
								<?php if ($course['is_free_course'] == 1) : ?>
									<h5><?php echo site_phrase('free'); ?></h5>
								<?php else:?>
									<?php if ($course['discount_flag'] == 1) : ?>
										<h5><?php echo currency($course['discounted_price']); ?></h5>
										<p><del><?php echo currency($course['price']); ?></del></p>
									<?php else : ?>
										<h5><?php echo currency($course['price']); ?></h5>
									<?php endif; ?>
								<?php endif?>
							</div>
							<div class="corses-price-right">
								<i class="fa-regular fa-clock"></i>
								<p><?php echo $course_duration; ?></p>
							</div>
							</div>
						</div>
						</div>
					</div>
				</div>
				<?php endforeach;?>
			</div>
		</div>
	</div>
	<h4 class="s_title_one pb-20"><?php echo get_phrase('Description')?></h4>
	<p class="s_info_one pb-30"><?php echo $bundle_details['bundle_details']; ?></p>
	
	<div class="s_review">
		<h4 class="title"><?php echo get_phrase('Bundle review')?></h4>
		<p class="date"></p>
		<p class="rating-no"><?php echo $ratings->num_rows()?></p>
		<div class="rating-icon">
		<?php //Bundle Rating
			$ratings = $this->course_bundle_model->get_bundle_wise_ratings($bundle_details['id']);
			$bundle_total_rating = $this->course_bundle_model->sum_of_bundle_rating($bundle_details['id']);
			if ($ratings->num_rows() > 0) {
				$bundle_average_ceil_rating = ceil($bundle_total_rating / $ratings->num_rows());
			}else {
				$bundle_average_ceil_rating = 0;
			}
		?>
			<?php for($i = 1; $i <= 5; $i++):?>
				<?php if ($i <= $bundle_average_ceil_rating): ?>
					<img src="<?php echo base_url('assets/frontend/default-new/image/icon/star-solid.svg')?>" alt="" />
				<?php else: ?>
					<img src="<?php echo base_url('assets/frontend/default-new/image/icon/star-solid-2.svg')?>" alt="" />
				<?php endif; ?>
			<?php endfor; ?>			
		</div>
	</div>
	</div>
</section>
<!-- End Instructor -->