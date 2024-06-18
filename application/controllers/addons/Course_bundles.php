<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_bundles extends CI_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        $this->load->model('addons/course_bundle_model');
        $this->load->model('crud_model');
        $this->load->model('email_model');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    function index(){
        $rows = $this->course_bundle_model->get_active_bundle();
        $config = array();
        $config = pagintaion($rows->num_rows(), 6);
        $config['base_url']  = site_url('course_bundles/');
        $this->pagination->initialize($config);

        $this->db->where('status', 1);
        $page_data['course_bundles'] = $this->db->get('course_bundle', $config['per_page'], $this->uri->segment(2));

        $page_data['page_name'] = "course_bundle";
        $page_data['page_title'] = site_phrase('course_bundle');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

    function search($search_string = ""){
        if (isset($_GET['string']) && !empty($_GET['string'])) {
            $search_string = $_GET['string'];
        }else {
            redirect(site_url('course_bundles'), 'refresh');
        }

        $page_data['course_bundles'] = $this->course_bundle_model->get_bundle_by_search_string($search_string);
        $page_data['search_string'] = $search_string;
        $page_data['page_name'] = "course_bundle";
        $page_data['page_title'] = site_phrase('course_bundle');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

    function load_more_courses_in_bundle($bundle_id = "", $limit = ""){
        $page_data['bundle_details'] = $this->course_bundle_model->get_bundle($bundle_id)->row_array();
        if($limit > 3){
            $page_data['courses'] = $this->course_bundle_model->get_courses_by_bundle_id($bundle_id, $limit)->result_array();
        }
        $this->load->view('frontend/'.get_frontend_settings('theme').'/load_more_courses_in_bundle', $page_data);
    }

    function bundle_details($bundle_id = ""){
        if($bundle_id == "" || is_numeric($bundle_id) != true)
        redirect(site_url('course_bundles'), 'refresh');

        $page_data['bundle_details'] = $this->course_bundle_model->get_bundle_details($bundle_id)->row_array();
        $page_data['instructor_details'] = $this->user_model->get_all_user($page_data['bundle_details']['user_id'])->row_array();
        $page_data['page_name'] = "bundle_details";
        $page_data['page_title'] = site_phrase('bundle_details');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

    function current_price_of_selected_courses($course_ids = array()){
        $current_price = 0;
        foreach($course_ids as $course_id):
            $this->db->or_where('id', $course_id);
        endforeach;
        $courses = $this->db->get('course')->result_array();

        foreach($courses as $course):
            if($course['is_free_course'] !=1):
                if($course['discount_flag'] == 1){
                    $current_price += $course['discounted_price'];
                }else{
                    $current_price += $course['price'];
                }
            endif;
        endforeach;

        return $current_price;
    }

    function buy($bundle_id = ""){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        if($bundle_id == ""){
            $this->session->set_flashdata('error_message', get_phrase('please_enter_numeric_valid_course_id'));
            redirect(site_url('course_bundles'), 'refresh');
        }

        $bundle_details = $this->course_bundle_model->get_bundle_details($bundle_id)->row_array();
        $instructor_details = $this->user_model->get_all_user($bundle_details['user_id'])->row_array();
        $bundle_courses = $this->course_bundle_model->get_all_courses_by_bundle_id($bundle_details['id'])->result_array();

        $included_course_ids = array();
        foreach($bundle_courses as $bundle_course){
            $included_course_ids[] = $bundle_course['id'];
        }
        $current_price_of_selected_courses = $this->current_price_of_selected_courses($included_course_ids);


        if($current_price_of_selected_courses > $bundle_details['price']){
            $discount_flag = 1;
        }elseif($current_price_of_selected_courses < $bundle_details['price']){
            $current_price_of_selected_courses = $bundle_details['price'];
            $discount_flag = 0;
        }else{
            $discount_flag = 0;
        }



        $items = array();
        $total_payable_amount = 0;

        //item detail
        $item_details['id'] = $bundle_details['id'];
        $item_details['title'] = $bundle_details['title'];
        $item_details['thumbnail'] = base_url('uploads/course_bundle/banner/'.$bundle_details['banner']);
        $item_details['creator_id'] = $bundle_details['user_id'];
        $item_details['discount_flag'] = $discount_flag;
        $item_details['discounted_price'] = $bundle_details['price'];
        $item_details['price'] = $current_price_of_selected_courses;

        $item_details['actual_price'] = $current_price_of_selected_courses;
        $item_details['sub_items'] = array();

        $items[] = $item_details;
        $total_payable_amount += $bundle_details['price'];
        //ended item detail

        //included tax
        //$total_payable_amount = round($total_payable_amount + ($total_payable_amount/100) * get_settings('course_selling_tax'), 2);

        //common structure for all payment gateways and all type of payment
        $data['total_payable_amount'] = $total_payable_amount;
        $data['items'] = $items;
        $data['is_instructor_payout_user_id'] = false;
        $data['payment_title'] = get_phrase('pay_for_course_bundle');
        $data['success_url'] = site_url('addons/course_bundles/success_bundle_payment');
        $data['cancel_url'] = site_url('payment');
        $data['back_url'] = site_url('bundle_details/'.$bundle_details['id'].'/'.slugify($bundle_details['title']));
        $this->session->set_userdata('payment_details', $data);

        redirect(site_url('payment'), 'refresh');

    }

    function success_bundle_payment($payment_method = ""){
        //STARTED payment model and functions are dynamic here
        $response = false;
        $user_id = $this->session->userdata('user_id');
        $payment_details = $this->session->userdata('payment_details');
        $payment_gateway = $this->db->get_where('payment_gateways', ['identifier' => $payment_method])->row_array();
        $model_name = strtolower($payment_gateway['model_name']);
        if($payment_gateway['is_addon'] == 1 && $model_name != null){
            $this->load->model('addons/'.strtolower($payment_gateway['model_name']));
        }

        if($model_name != null){
            $payment_check_function = 'check_'.$payment_method.'_payment';
            $response = $this->$model_name->$payment_check_function($payment_method);
        }
        //ENDED payment model and functions are dynamic here
        
        if ($response === true) {
            $data['user_id'] = $this->session->userdata('user_id');
            $data['bundle_creator_id'] = $payment_details['items'][0]['creator_id'];
            $data['bundle_id'] = $payment_details['items'][0]['id'];
            $data['payment_method'] = $payment_method;

            if(isset($_GET['session_id'])){
                $data['session_id'] = $_GET['session_id'];
            }

            if(isset($_GET['transaction_id'])){
                $data['transaction_id'] = $_GET['transaction_id'];
            }

            $data['amount'] = $payment_details['total_payable_amount'];
            $data['date_added'] = strtotime(date('d M Y'));

            $this->db->insert('bundle_payment', $data);
            
            $this->session->set_userdata('payment_details', []);
            $this->session->set_flashdata('flash_message', site_phrase('payment_successfully_done'));
            redirect('home/my_bundles', 'refresh');
        }else{
            $this->session->set_flashdata('error_message', site_phrase('payment_failed'));
            redirect($back_url, 'refresh');
        }
    }

    function my_bundles(){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }
        $page_data['page_name'] = "my_bundles";
        $page_data['page_title'] = site_phrase('my_bundles');
        $page_data['my_bundles'] = $this->course_bundle_model->my_bundles();
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

    function my_bundles_by_search_string(){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }
        $search_string = html_escape($this->input->post('search_string'));
        $page_data['my_bundles'] = $this->course_bundle_model->my_bundles_by_search_string($search_string);
        $this->load->view('frontend/'.get_frontend_settings('theme').'/user_purchase_bundle', $page_data);
    }

    public function lesson($slug = "", $bundle_id = "", $course_id = "", $lesson_id = "") {
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        if($this->course_bundle_model->get_bundle($bundle_id)->row('status') != 1){
            $this->session->set_flashdata('error_message', get_phrase('this_bundle_is_currently_disabled'));
            redirect('home/my_bundles', 'refresh');
        }

        if(get_bundle_validity($bundle_id) == 'invalid'){
            $this->session->set_flashdata('error_message', get_phrase('please_buy_the_bundle_first'));
            redirect('home/my_bundles', 'refresh');
        }elseif(get_bundle_validity($bundle_id) == 'expire'){
            $this->session->set_flashdata('error_message', get_phrase('please_renew_your_bundle'));
            redirect('home/my_bundles', 'refresh');
        }

        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
        $page_data['course_details'] = $this->crud_model->get_course_by_id($course_id)->row_array();

        if($course_details['status'] != 'active'){
            $this->session->set_flashdata('error_message', get_phrase('this_course_is_currently_disabled'));
            redirect('home/my_bundles', 'refresh');
        }
        
        $sections = $this->crud_model->get_section('course', $course_id);
        if ($sections->num_rows() > 0) {
            $page_data['sections'] = $sections->result_array();
            if ($lesson_id == "") {
                $default_section = $sections->row_array();
                $page_data['section_id'] = $default_section['id'];
                $lessons = $this->crud_model->get_lessons('section', $default_section['id']);
                if ($lessons->num_rows() > 0) {
                    $default_lesson = $lessons->row_array();
                    $lesson_id = $default_lesson['id'];
                    $page_data['lesson_id']  = $default_lesson['id'];
                    
                }else {
                    $page_data['page_name'] = 'empty';
                    $page_data['page_title'] = site_phrase('no_lesson_found');
                    $page_data['page_body'] = site_phrase('no_lesson_found');
                }
            }else {
                $page_data['lesson_id']  = $lesson_id;
                $section_id = $this->db->get_where('lesson', array('id' => $lesson_id))->row()->section_id;
                $page_data['section_id'] = $section_id;
            }
            
        }else {
            $page_data['sections'] = array();
            $page_data['page_name'] = 'empty';
            $page_data['page_title'] = site_phrase('no_section_found');
            $page_data['page_body'] = site_phrase('no_section_found');
        }
            // $page_data['lesson_details'] = $this->db->get_where('lesson', array('course_id' => $course_details['id'], 'id'=>$lesson_id))->row_array();
        $lesson_id = $this->crud_model->update_last_played_lesson($course_id, $lesson_id);
        $page_data['lesson_details'] = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();

        $page_data['drip_content_settings']  = json_decode(get_settings('drip_content_settings'), true);
        $page_data['watch_history']  = $this->crud_model->get_watch_histories($this->session->userdata('user_id'), $course_id)->row_array();
        $page_data['bundle_id']  = $bundle_id;
        $page_data['course_id']  = $course_id;
        $page_data['page_name']  = 'lessons';
        $page_data['page_title'] = $course_details['title'];
        $this->load->view('lessons/index', $page_data);
    }

    function invoice($payment_id = ''){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        $page_data['bundle_payment'] = $this->course_bundle_model->get_bundle_payment($payment_id)->row_array();
        
        $page_data['bundle_details'] = $this->course_bundle_model->get_bundle($page_data['bundle_payment']['bundle_id'])->row_array();
        //student ID
        $page_data['student_details'] = $this->user_model->get_all_user($page_data['bundle_payment']['user_id'])->row_array();

        //instructor ID
        $page_data['instructor_details'] = $this->user_model->get_all_user($page_data['bundle_payment']['bundle_creator_id'])->row_array();

        $page_data['page_name']  = 'bundle_invoice';
        $page_data['page_title'] = site_phrase('invoice');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

    function bundle_purchase_history($bundle_id = ""){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        $user_id = $this->session->userdata('user_id');
        $page_data['bundle_purchase_histories'] = $this->course_bundle_model->bundle_wise_purchase_history($bundle_id, $user_id);
        $this->load->view('frontend/'.get_frontend_settings('theme').'/bundle_purchase_history', $page_data);
    }

    function bundle_rating($bundle_id = ""){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        $user_id = $this->session->userdata('user_id');
        $page_data['user_bundle_rating'] = $this->course_bundle_model->get_user_bundle_rating($user_id, $bundle_id);
        $page_data['bundle_id'] = $bundle_id;
        $this->load->view('frontend/'.get_frontend_settings('theme').'/user_bundle_rating', $page_data);
    }

    function update_bundle_rating($bundle_id = ""){
        if(!$this->session->userdata('user_login')){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        $user_id = $this->session->userdata('user_id');
        if(get_bundle_validity($bundle_id, $user_id) == 'valid'){
            if(html_escape($this->input->post('rating')) > 0){
                $this->course_bundle_model->update_bundle_rating($bundle_id, $user_id);
                echo 'success';
            }else{
                echo 0;
            }
        }else{
            echo 'expired';
        }
    }






    // SHOW STRIPE CHECKOUT PAGE
    public function stripe_checkout($payment_request_from = "") {
        if ($this->session->userdata('user_login') != 1 && $payment_request_from == 'from_web'){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        //checking price
        $page_data['payment_request_from'] = $payment_request_from;
        $page_data['user_details']    = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        $page_data['bundle_details'] = $this->course_bundle_model->get_bundle_details($this->session->userdata('checkout_bundle_id'))->row_array();
        $page_data['amount_to_pay']   = $this->session->userdata('checkout_bundle_price');
        $this->load->view('bundle_payment/stripe/stripe_checkout', $page_data);
    }

    // STRIPE CHECKOUT ACTIONS
    public function stripe_payment($user_id = "", $payment_request_from = "", $session_id = "") {
        if ($this->session->userdata('user_login') != 1 && $payment_request_from == 'from_web'){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        //THIS IS HOW I CHECKED THE STRIPE PAYMENT STATUS
        $response = $this->payment_model->stripe_payment($user_id, $session_id);

        if ($response['payment_status'] === 'succeeded') {
            // STUDENT ENROLMENT OPERATIONS AFTER A SUCCESSFUL PAYMENT

            $check_duplicate = $this->db->get_where('bundle_payment', array('user_id' => $user_id, 'session_id' => $session_id, 'transaction_id' => $response['transaction_id']))->num_rows();
            if($check_duplicate <= 0):
                $this->course_bundle_model->bundle_purchase('stripe', $response['paid_amount'], $response['transaction_id'], $session_id);
                $this->email_model->bundle_purchase_notification($user_id);

                $this->session->set_userdata('checkout_bundle_price', null);
                $this->session->set_userdata('checkout_bundle_id', null);
            else:
                $this->session->set_flashdata('error_message', site_phrase('session_time_out'));
                redirect('home/course_bundles', 'refresh');
            endif;

            if($payment_request_from == 'from_web'):
                $this->session->set_flashdata('flash_message', site_phrase('payment_successfully_done'));
                redirect('home/my_bundles', 'refresh');
            else:
                //for mobile
            endif;
        }else{
            if($payment_request_from == 'from_web'):
                $this->session->set_flashdata('error_message', $response['status_msg']);
                redirect('home', 'refresh');
            else:
                //for mobile
            endif;

        }

    }




    // SHOW PAYPAL CHECKOUT PAGE
    public function paypal_checkout($payment_request_from = "") {
        // true for web
        if ($this->session->userdata('user_login') != 1 && $payment_request_from == ''){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        //checking price
        if(empty($this->input->post('payment_request_from'))){
            $page_data['payment_request_from'] = $payment_request_from;
        }else{
            $page_data['payment_request_from'] = html_escape($this->input->post('payment_request_from'));
        }
        $page_data['user_details']    = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        $page_data['amount_to_pay']   = $this->session->userdata('checkout_bundle_price');
        $this->load->view('bundle_payment/paypal/paypal_checkout', $page_data);
    }

    // PAYPAL CHECKOUT ACTIONS
    public function paypal_payment($user_id = "", $amount_paid = "", $paymentID = "", $paymentToken = "", $payerID = "", $payment_request_from = "") {
        if ($this->session->userdata('user_login') != 1 && $payment_request_from == ''){
            $this->session->set_flashdata('error_message', get_phrase('please_login_first'));
            redirect('home/login', 'refresh');
        }

        $paypal_keys = get_settings('paypal');
        $paypal = json_decode($paypal_keys);

        if ($paypal[0]->mode == 'sandbox') {
            $paypalClientID = $paypal[0]->sandbox_client_id;
            $paypalSecret   = $paypal[0]->sandbox_secret_key;
        }else{
            $paypalClientID = $paypal[0]->production_client_id;
            $paypalSecret   = $paypal[0]->production_secret_key;
        }

        //THIS IS HOW I CHECKED THE PAYPAL PAYMENT STATUS
        $status = $this->payment_model->paypal_payment($paymentID, $paymentToken, $payerID, $paypalClientID, $paypalSecret);
        if (!$status) {
            $this->session->set_flashdata('error_message', site_phrase('an_error_occurred_during_payment'));
            redirect('home', 'refresh');
        }

        $this->course_bundle_model->bundle_purchase('paypal', $amount_paid, $paymentID, $paymentToken);
        $this->email_model->bundle_purchase_notification($user_id);

        $this->session->set_userdata('checkout_bundle_price', null);
        $this->session->set_userdata('checkout_bundle_id', null);

        if($payment_request_from == 'from_web'):
            $this->session->set_flashdata('flash_message', site_phrase('payment_successfully_done'));
            redirect('home/my_bundles', 'refresh');
        else:
            //for mobile
        endif;

    }
    //Paypal End

    
}