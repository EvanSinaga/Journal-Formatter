<?php
class Auth extends CI_Controller
{
    //KONSTRUKTOR KELAS AUTH
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }


    //FUNGSI INDEX CONTROLLER AUTH
    public function index()
    {
        $data['title'] = "Login";
        $this->form_validation->set_rules('nip', 'NIP', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == false) {
            //jika tidak lolos validasi
            $this->load->view('templates/header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/footer');
        } else {
            //jika lolos validasi
            $this->login();
        }
    }


    //FUNGSI LOGIN
    private function login()
    {
        $nip = $this->input->post('nip');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['nip' => $nip])->row_array();

        if ($user) {
            // jika user ditemukan di dalam database, cek password
            if ($password == $user['password']) {
                $data = ['nip' => $user['nip']];
                $this->session->set_userdata($data);
                redirect('artikel');
            } else {
                // jika password salah
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password!</div>');
                redirect('auth');
            }
        } else {
            // jika NIP tidak ditemukan di dalam database
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">NIP not found.</div>');
            redirect('auth');
        }
    }


    //FUNGSI LOGOUT
    public function logout()
    {
        // mengosongkan folder file temporary
        delete_files('./assets/temp/');

        $this->session->unset_userdata('word');
        $this->session->unset_userdata('nip');

        //menampilkan pesan berhasil logout
        $this->session->set_flashdata(
            'message',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Successfully logged out!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
            </div>'
        );
        redirect('auth');
    }
}
