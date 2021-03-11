<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/commonmodel');
    }

    public function index()
    {
        $logo_data = $this->commonmodel->getsettingdata(1);
        $this->data['image_logo'] = $logo_data['logo_image'];
        $this->data['store_name'] = $logo_data['storename'];
        $this->data['page_title'] = 'Webdesk Soution';
        $session_data = $this->session->userdata('admin_session');
        if (isset($session_data) && !empty($session_data)) {
            redirect('admin/dashboard');
        }
        $this->data['errmsg'] = '';

        if ($this->input->post('username') && $this->input->post('password')) {
            // Check USER AND PASSWORD VALID OR NOT
            $query = $this->db->query("SELECT * from users where username='".$this->input->post('username')."' AND password='".md5($this->input->post('password'))."' AND status IN('yes')");
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                //SET SESSION
                $user_session = array('user_id' => $row['id'], 'firstname' => $row['firstname'], 'lastname' => $row['lastname'], 'email' => $row['email'], 'username' => $row['username']);
                $this->session->set_userdata('admin_session', $user_session);
                redirect('admin/dashboard');
            } else {
                $data['errmsg'] = 'Invalid username or password';
            }
        }
        $this->load->view('admin/login.php', $this->data);
    }

    // Verify Username And Password
    public function verify()
    {
        $logo_data = $this->commonmodel->getsettingdata(1);
        $this->data['image_logo'] = $logo_data['logo_image'];
        $this->data['store_name'] = $logo_data['storename'];
        $this->data['page_title'] = $this->lang->line('LOGIN_TITLE');
        $this->data['errmsg'] = '';
        if ($this->input->post('username') && $this->input->post('password')) {
            $query = $this->db->query("SELECT * from users where username='".$this->input->post('username')."' AND password='".md5($this->input->post('password'))."' AND status IN('yes')");

            if ($query->num_rows() > 0) {
                $row = $query->row_array();

                //SET SESSION
                $user_session = array('user_id' => $row['id'], 'firstname' => $row['firstname'], 'lastname' => $row['lastname'], 'email' => $row['email'], 'username' => $row['username']);
                $this->session->set_userdata('admin_session', $user_session);

                redirect('admin/dashboard');
               
            } else {
                $this->data['errmsg'] = 'Invalid username or password';
            }
        }
        $this->load->view('admin/login.php', $this->data);
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('admin/login', 'refresh');
    }
}
