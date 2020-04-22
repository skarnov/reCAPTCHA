<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class home extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $user_id = $this->session->userdata('user_id');
        if ($user_id != NULL){
            redirect('user', 'refresh');
        }        
    }

    public function save_user() {
        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|is_unique[tbl_users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('resume_id', 'Resume Category', "trim|required");
        $this->form_validation->set_rules('sex', 'Sex', "trim|required");
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|is_unique[tbl_users.mobile]');
        if ($this->form_validation->run() == FALSE) {
            $data = array();
            $data['title'] = 'New Profile';
            $data['all_resumes'] = $this->User_model->select_all_resumes();
            $data['main'] = $this->load->view('website/create_account', $data, true);
            $this->load->view('website/master', $data);
        } else {
            /* Google reCAPTCHA API*/
            $response = $this->input->post('g-recaptcha-response', true);
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $reCAPTCHA = array(
		'secret' => 'your_secret_key',
		'response' => $response
            );
            $options = array(
		'http' => array (
                    'method' => 'POST',
                    'content' => http_build_query($reCAPTCHA)
		)
            );
            $context  = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captcha_success=json_decode($verify);
            if ($captcha_success->success==false) {
                $this->session->set_flashdata('save_user', 'Please Verify That You Are Not Robot');
                redirect('home/index');
            } 
            else if ($captcha_success->success==true) {
                $data = array();
                $data['first_name'] = $this->input->post('first_name', true);
                $data['last_name'] = $this->input->post('last_name', true);
                $data['email'] = $this->input->post('email', true);
                $data['password'] = $this->input->post('password', true);
                $data['fk_resume_id'] = $this->input->post('resume_id', true);
                $data['sex'] = $this->input->post('sex', true);
                $data['mobile'] = $this->input->post('mobile', true);
                $data['user_type'] = 2;
                $data['created_at'] = date("Y-m-d H:i:s");
                $this->db->insert('tbl_users', $data);
                $user_id = $this->db->insert_id();
                $generated_PIN = $this->User_model->generate_PIN();
                $verification = array();
                $verification['fk_user_id'] = $user_id;
                $verification['verification_property'] = $data['email'];
                $verification['pin_code'] = $generated_PIN;
                $verification['status'] = 1;
                $verification['created_at'] = date("Y-m-d H:i:s");
                $this->db->insert('tbl_verifications', $verification);
                $this->User_model->send_email_verification_link($generated_PIN, $data['email']);
                $this->session->set_flashdata('save_user', 'Account Created Successfully! Check Your Email For Varification');
                redirect('home/index');
            }            
        }
    }
}