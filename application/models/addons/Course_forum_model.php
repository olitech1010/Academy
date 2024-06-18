<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Course_forum_model extends CI_Model {
    function __construct() {
        parent::__construct();
        date_default_timezone_set(get_settings('timezone'));
    }

    function get_questions($question_id = ""){
        if($question_id > 0){
            $this->db->where('id', $question_id);
        }
        return $this->db->get('course_forum');
    }

    function get_child_question($parent_question_id = "", $user_id = ""){
        if($user_id > 0){
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('is_parent', $parent_question_id);
        return $this->db->get('course_forum');
    }

    function get_course_wise_all_parent_questions($course_id = ""){
        $this->db->order_by('id', 'desc');
        $this->db->where('course_id', $course_id);
        $this->db->where('is_parent', 0);
        return $this->db->get('course_forum');
    }
    
    function get_course_wise_limited_questions($course_id = "", $limit = 10, $starting_value = 0){
        $this->db->order_by('id', 'desc');
        $this->db->where('course_id', $course_id);
        $this->db->where('is_parent', 0);
        return $this->db->get('course_forum', $limit, $starting_value);
    }

    function user_vote($question_id = ""){
        $array_data = array();

        $user_id = $this->session->userdata('user_id');
        $this->db->where('id', $question_id);
        $upvoted_user_ids = $this->db->get_where('course_forum')->row('upvoted_user_id');
        if($upvoted_user_ids == 'null' || $upvoted_user_ids == null){
            $data['upvoted_user_id'] = json_encode(array(0 => $user_id));
            $return_type =  'upvoted';
        }else{
            $array_of_user_id = json_decode($upvoted_user_ids);
            $array_data = $array_of_user_id;

            if(in_array($user_id, $array_of_user_id)){
                $key = array_search($user_id, $array_of_user_id);
                unset($array_data[$key]);
                $array_data = array_values($array_data);
                $return_type =  'unvoted';
            }else{
                array_push($array_data, $user_id);
                $return_type = 'upvoted';
            }

            $data['upvoted_user_id'] = json_encode($array_data);

        }

        $this->db->where('id', $question_id);
        $this->db->update('course_forum', $data);

        return $return_type;

    }

    function publish_question(){
        $user_id = $this->session->userdata('user_id');
        $is_valid_user = $this->db->get_where('enrol', array('user_id' => $user_id, 'course_id' => html_escape($this->input->post('course_id'))))->num_rows();



        $data['title'] = html_escape($this->input->post('title'));
        $data['course_id'] = html_escape($this->input->post('course_id'));
        $data['is_parent'] = html_escape($this->input->post('is_parent'));
        $data['date_added'] = time();
        $data['user_id'] = $user_id;

        $instructors = $this->crud_model->get_course_instructors_id($data['course_id']);
        if(enroll_status($data['course_id'], $user_id) == 'valid' || in_array($user_id, $instructors) || $this->session->userdata('admin_login') == '1'){
            $description = upload_description_images($_POST['description'], 'uploads/description-images');
            $data['description'] = remove_js($description);
            $this->db->insert('course_forum', $data);

            //Send notification
            $title = get_phrase('Forum Queries').': '.$data['title'];
            $course_title = $this->crud_model->get_course_by_id($data['course_id'])->row('title');
            $descriptiion = $data['description'].' <a href="'.site_url('home/lesson/'.slugify($course_title).'/'.$data['course_id']).'?tab=forum-content">'.get_phrase('View').'</a>';
            $this->send_notification($data['course_id'], $user_id, $data['is_parent'], $title, $descriptiion);

        }
    }


    function send_notification($course_id = "", $user_id = "", $is_parent = "", $title = "", $description = ""){
        if($is_parent == 0 && enroll_status($course_id, $user_id) == 'valid'){
            //System notification
            $instructors = $this->crud_model->get_course_instructors_id($course_id);
            foreach($instructors as $instructor_id):
                $this->email_model->notify('forum', $instructor_id, $title, $description, $user_id);
            endforeach;
        }else{

            $sent_user = array();

            $this->db->group_start();
            $this->db->where('is_parent', $is_parent);
            $this->db->or_where('id', $is_parent);

            $this->db->group_end();
            $this->db->where('course_id', $course_id);
            $this->db->where('user_id !=', $user_id);

            $all_comment_of_this_parent = $this->db->get('course_forum')->result_array();

            foreach($all_comment_of_this_parent as $comment){
                if(!in_array($comment['user_id'], $sent_user)){
                    $this->email_model->notify('forum', $comment['user_id'], $title, $description, $user_id);
                }
                $sent_user[] = $comment['user_id'];
            }
        }
    }

    function search_questions($course_id = ""){
        $searching_keyword = html_escape($this->input->post('searching_value'));
        $this->db->like('title', $searching_keyword);
        $this->db->or_like('description', $searching_keyword);
        $this->db->where('course_id', $course_id);
        $this->db->where('is_parent', 0);
        return $this->db->get('course_forum');
    }

    function publish_question_comment($course_id = "", $question_id = ""){
        $user_id = $this->session->userdata('user_id');
        $is_valid_user = $this->db->get_where('enrol', array('user_id' => $user_id, 'course_id' => $course_id))->num_rows();

        $data['user_id'] = $user_id;
        $data['course_id'] = $course_id;
        $data['is_parent'] = $question_id;
        $data['date_added'] = time();

        if($is_valid_user > 0 || $this->session->userdata('admin_login') == '1'){
            $description = upload_description_images($_POST['description'], 'uploads/description-images');
            $data['description'] = remove_js($description);
            $this->db->insert('course_forum', $data);

            //Send notification
            $title = get_phrase('Comment on Forum');
            $course_title = $this->crud_model->get_course_by_id($course_id)->row('title');
            $descriptiion = $data['description'].' <a href="'.site_url('home/lesson/'.slugify($course_title).'/'.$course_id).'?tab=forum-content">'.get_phrase('View').'</a>';
            $this->send_notification($data['course_id'], $user_id, $data['is_parent'], $title, $descriptiion);
        }
    }



}
