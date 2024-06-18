<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_forum extends CI_Controller {
    public function __construct() {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));
        
        $this->load->model('addons/course_forum_model');
        $this->load->database();
        $this->load->library('session');
    }


    function user_vote($question_id = ""){
        echo $this->course_forum_model->user_vote($question_id);
    }

    function add_new_question_form($course_id = ""){
        $data['course_id'] = $course_id;
        $this->load->view('lessons/add_new_question_form', $data);
    }

    function load_question_with_ajax($course_id = "", $limit = 10){
        $data['course_id'] = $course_id;
        $this->load->view('lessons/course_forum', $data);
    }

    function publish_question(){
        $this->course_forum_model->publish_question();
        echo 'success';
    }

    function search_questions($course_id = ""){
        $data['searching_value'] = html_escape($this->input->post('searching_value'));
        $data['questions'] = $this->course_forum_model->search_questions($course_id)->result_array();
        $data['course_id'] = $course_id;
        $this->load->view('lessons/course_forum', $data);
    }

    function show_more_questions($course_id = "", $starting_value = ""){
        $data['starting_value'] = $starting_value+10;
        $data['course_id'] = $course_id;
        $data['questions'] = $this->course_forum_model->get_course_wise_limited_questions($course_id, 10, $starting_value)->result_array();
        $this->load->view('lessons/show_more_questions', $data);
    }

    function question_comments($question_id = ""){
        $data['question'] = $this->course_forum_model->get_questions($question_id)->row_array();
        $data['user_details'] = $this->user_model->get_all_user($data['question']['user_id'])->row_array();
        $data['question_comments'] = $this->course_forum_model->get_child_question($question_id)->result_array();
        $this->load->view('lessons/question_comments', $data);
    }

    function publish_question_comment($course_id = "", $question_id = ""){
        $this->course_forum_model->publish_question_comment($course_id, $question_id);
        $this->question_comments($question_id);
    }

    function delete_question($question_id = "", $called_from = ""){
        $question = $this->course_forum_model->get_questions($question_id)->row_array();
        $parent_question_id = $question['is_parent'];
        $course_id = $question['course_id'];
        
        if($question['user_id'] == $this->session->userdata('user_id')){
            remove_description_images($question['description']);

            $this->db->where('id', $question_id);
            $this->db->delete('course_forum');
        }else{
            echo false;
        }

        if($called_from == "reply_question"){
            $this->question_comments($parent_question_id);
        }else{
            $this->load_question_with_ajax($course_id);
        }
    }



}
