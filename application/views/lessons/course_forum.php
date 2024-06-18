<?php $forum =&get_instance();
$forum->load->model('addons/course_forum_model');

//check from the request
if(!isset($searching_value)){
	//this code user for general page
	$number_of_total_questions = $forum->course_forum_model->get_course_wise_all_parent_questions($course_id)->num_rows();
	$questions = $forum->course_forum_model->get_course_wise_limited_questions($course_id)->result_array();
	$searching_value = null;
}else{
	//this code user for searching page
	$number_of_searching_rows = count($questions);
}
?>
<div id="question-body">
	<div class="row justify-content-center">
		<div class="col-md-8 pt-3">

			<?php if(isset($searching_value)): ?>
				<div class="row pb-3">
					<div class="col-md-12 p-0">
						<a class="btn-primary py-2 px-3 rounded-50" href="javascript:;" onclick="load_questions('<?= $course_id; ?>')"><i class="fas fa-arrow-left"></i> <?= site_phrase('all_questions'); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-md-12 question-search p-0 mt-2">
					<input id="question_search_input" type="text" onkeyup="enter_event(event)" value="<?= $searching_value; ?>" class="form-control m-0 question-search-input" placeholder="<?= site_phrase('search_questions'); ?>..." name="">
					<button id="searching_btn" class="btn btn-secondary w-100" onclick="search_questions('<?= $course_id; ?>');"><i class="fas fa-search"></i></button>
				</div>
			</div>
		</div>
		<div class="col-md-8 pt-5 pb-3">
			<?php if(!isset($searching_value)): ?>
				<span class="question-header-left mt-2 d-inline-block"><?php echo $number_of_total_questions; ?> <?= site_phrase('questions_in_this_course'); ?>.</span>
			<?php else: ?>
				<span class="question-header-left mt-2 d-inline-block"><?php echo site_phrase('found').' '.$number_of_searching_rows; ?> <?= site_phrase('questions'); ?></span>
			<?php endif; ?>
			<a href="javascript:;" class="question-header-right btn btn-primary float-end" onclick="load_question_form('<?= $course_id; ?>')">
				<i class="fas fa-plus"></i>
			</a>
		</div>
		<?php foreach($questions as $key=>$question):
			$user_details = $this->user_model->get_all_user($question['user_id'])->row_array();
			if($question['upvoted_user_id'] == null || $question['upvoted_user_id'] == 'null'){
				$upvoted_user_ids = json_encode(array());
			}else{
				$upvoted_user_ids = $question['upvoted_user_id'];
			}
			if(in_array($this->session->userdata('user_id'), json_decode($upvoted_user_ids))){
				$upvoted_user = true;
			}else{
				$upvoted_user = false;
			}
			$question_comments = $forum->course_forum_model->get_child_question($question['id']);
			$commented_user = $forum->course_forum_model->get_child_question($question['id'], $this->session->userdata('user_id'))->num_rows();
			?>
			<div class="col-md-8 border-top user-course-questions py-3 hide-search-processing">
				<div class="row">
					<div class="col-md-10 col-lg-10 col-xl-11 cursor-pointer" onclick="question_comments('<?= $question['id']; ?>')">
						<h6><?= $question['title']; ?></h6>
						<p class="text-14px"><?= nl2br($question['description']); ?></p>
					</div>
					<div class="col-md-2 col-lg-2 col-xl-1 p-0">
						<button class="border-0 mt-2 icon-upvot-comment <?php if($upvoted_user == true){ echo 'text-primary'; }else{ echo 'text-mute'; } ?>" onclick="user_vote('<?= $question['id']; ?>', this)"><span id="count-upvote-<?= $question['id']; ?>"><?= count(json_decode($upvoted_user_ids)); ?></span> <i class="far fa-thumbs-up width-10-px m-0"></i></button>

						<button class="border-0 mt-2 icon-upvot-comment <?php if($commented_user > 0){ echo 'text-primary'; }else{ echo 'text-mute'; } ?>"  onclick="question_comments('<?= $question['id']; ?>')"><span><?= $question_comments->num_rows(); ?></span> <i class="far fa-comment-alt width-10-px m-0"></i></button>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-12 text-14px">
						<p class="pe-2">
							<a class="fw-bold" href="<?= site_url('home/instructor_page/').$question['user_id']; ?>" target="_blank">
								<span>
									<img class="rounded-circle" src="<?php echo $this->user_model->get_user_image_url($question['user_id']); ?>" width="25" height="25">
								</span>
								<?= $user_details['first_name'].' '.$user_details['last_name']; ?>
							</a>
							<span class="text-muted">
								, <?= get_past_time($question['date_added']); ?>
							</span>
							<?php if($this->session->userdata('user_id') == $question['user_id']): ?>
								<a class="float-end text-mute" href="javascript:;" onclick="if(confirm('<?php echo get_phrase('Are you sure?'); ?>')) delete_question('<?= $question['id']; ?>')"><i class="far fa-trash-alt"></i></a>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php if(!isset($searching_value) && $number_of_total_questions > 10):
			$starting_value = $key+1; ?>
			<div class="col-md-8 p-0 hide-search-processing">
				<a href="javascript:;" class="btn btn-light w-100 fw-bold mt-5" onclick="show_more_questions(this, '<?= $course_id; ?>', '<?= $starting_value; ?>')"><?= site_phrase('show_more'); ?></a>
			</div>
		<?php elseif(isset($searching_value) && count($questions) <= 0): ?>
			<div class="col-md-8 py-5 hide-search-processing">
				<h5 class="text-center py-5 text-muted"><?php echo site_phrase('there_is_no_data_related_to_your_question'); ?> !!</h5>
			</div>
		<?php endif; ?>
	</div>
</div>