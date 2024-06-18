<div class="col-md-12 p-4">
	<!--Show more loading icon-->
	<div class="row justify-content-center">
	    <div class="col-md-7 p-0 d-hidden" id="show_more_loding_icon">
	        <img width="100%" src="<?php echo base_url('assets/global/gif/page-loader.gif'); ?>">
	        <img width="100%" src="<?php echo base_url('assets/global/gif/page-loader.gif'); ?>">
	        <img width="100%" src="<?php echo base_url('assets/global/gif/page-loader.gif'); ?>">
	    </div>
	    <div class="col-md-7 p-0 text-center hidden py-5 my-5" id="show_questions">
	        <img width="60" class="my-5" src="<?php echo base_url('assets/global/gif/page-loader-2.gif'); ?>">
	    </div>
	</div>
</div>
<script type="text/javascript">
	function user_vote(question_id, e){
		$.ajax({
			url: '<?= site_url('addons/course_forum/user_vote/'); ?>'+question_id,
			success: function(response){
				//alert('success');
				var count_upvote_value = $('#count-upvote-'+question_id).text();
				if(response == 'upvoted'){
					$('#count-upvote-'+question_id).html(Number(count_upvote_value)+1);
					$(e).removeClass('text-mute');
					$(e).addClass('text-primary');
				}else{
					$('#count-upvote-'+question_id).html(Number(count_upvote_value)-1);
					$(e).removeClass('text-primary');
					$(e).addClass('text-mute');
				}
			}
  		});
	}

	function load_question_form(course_id){
		$.ajax({
			url: '<?= site_url('addons/course_forum/add_new_question_form/'); ?>'+course_id,
			success: function(response){
				$('#forum-content').html(response);
			}
  		});
	}

	function publish_question(course_id){
		var question_title = $('#questionTitle').val();
		var question_description = $('#questionDescription').val();
		if(question_title != ""){
			$.ajax({
				type: "POST",
				url: "<?= site_url('addons/course_forum/publish_question'); ?>",
				data: {title: question_title, description: question_description, course_id: course_id, is_parent: 0},
				success: function(response){
					if(response == 'success'){
						$.ajax({
							url: '<?= site_url('addons/course_forum/load_question_with_ajax/'); ?>'+course_id,
							success: function(response){
								$('#forum-content').html(response);
								toastr.success('<?= site_phrase('your_question_has_been_successfully_published'); ?>.');
							}
				  		});
				  	}
				}
			});
		}else{
			toastr.error('<?= site_phrase('please_write_your_question_title_or_summary'); ?>.');
		}
	}

	function load_questions(course_id){
		$('.remove-active').removeClass('active');
		$('#forum-tab').addClass('active');

		$('#forum-content').hide();
		$('#show_questions').show();
		$.ajax({
			url: '<?= site_url('addons/course_forum/load_question_with_ajax/'); ?>'+course_id,
			success: function(response){
				setTimeout(function(){
					$('#show_questions').hide();
					$('#forum-content').show();
					$('#forum-content').html(response);
				},200);
			}
  		});
	}


	function enter_event(event){
		if (event.keyCode === 13) {
	    	document.getElementById("searching_btn").click();
		}
	}
	function search_questions(course_id){
		var searching_value = $('#question_search_input').val();
		if(searching_value != ""){
			$('#show_more_loding_icon').show();
			$('.hide-search-processing').hide();
			$.ajax({
				type: "POST",
				url: "<?= site_url('addons/course_forum/search_questions/'); ?>"+course_id,
				data: {searching_value: searching_value},
				success: function(response){
					setTimeout(function(){
						$('#show_more_loding_icon').hide();
						$('#forum-content').html(response);
					},200);
				}
			});
		}else{
			load_questions(course_id);
		}
	}

	function show_more_questions(e, course_id, starting_value){
		$(e).hide();
		$('#show_more_loding_icon').show();
		$.ajax({
			url: '<?= site_url('addons/course_forum/show_more_questions/'); ?>'+course_id+'/'+starting_value,
			success: function(response){
				setTimeout(function(){
					$('#show_more_loding_icon').hide();
					$('#question-body').append(response);
				},200);
			}
  		});
	}

	function question_comments(question_id){
		$.ajax({
			url: '<?= site_url('addons/course_forum/question_comments/'); ?>'+question_id,
			success: function(response){
				$('#forum-content').html(response);
			}
  		});
	}

	function publish_question_comment(course_id, question_id){
		var description = $('#questionCommentDescription').val();
		if(description != ""){
			$.ajax({
				type: 'post',
				url: '<?= site_url('addons/course_forum/publish_question_comment/'); ?>'+course_id+'/'+question_id,
				data: {description : description},
				success: function(response){
					$('#forum-content').html(response);
				}
	  		});
		}
	}

	function delete_question(question_id, called_from){
		$.ajax({
			url: '<?= site_url('addons/course_forum/delete_question/'); ?>'+question_id+'/'+called_from,
			success: function(response){
				$('#forum-content').html(response);
			}
  		});
	}
</script>