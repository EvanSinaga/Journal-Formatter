<?php
class Artikel extends CI_Controller
{
    //KONSTRUKTOR KELAS ARTIKEL
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('pagination');
    }


    //FUNGSI INDEX CONTROLLER ARTIKEL
    public function index()
    {
        $data['title'] = "Articles";

        //Mengambil user
        $data['user'] = $this->db->get_where('user', ['nip' => $this->session->userdata('nip')])->row_array();

        //search
        if ($this->input->post('submit')) {
            //jika pencarian kata terisi
            $data['word'] = $this->input->post('word');
            $this->session->set_userdata('word', $data['word']);
        } else {
            //jika pencarian kata belom terisi atau menuju hasil pencarian kata halaman berikutnya
            $data['word'] = $this->session->userdata('word');
        }

        //Query pencarian kata
        $this->db->like('judul', $data['word']);
        $this->db->or_like('author', $data['word']);
        $this->db->or_like('year', $data['word']);
        $this->db->or_like('volume', $data['word']);
        $this->db->or_like('number', $data['word']);
        $this->db->or_like('keywords', $data['word']);
        $this->db->from('artikel');

        //Konfigurasi ekstra untuk pagination, diluar file pagination.php
        $config['total_rows'] = $this->db->count_all_results();
        $config['per_page'] = 10;
        $this->pagination->initialize($config);

        //Untuk menampilkan jumlah hasil pencarian
        $data['total_rows'] = $config['total_rows'];

        //Menampilkan artikel secara keseluruhan ataupun yang dicari
        $data['start'] = $this->uri->segment(3);
        $data['artikel'] = $this->Artikel_model->tampil_data($config['per_page'], $data['start'], $data['word'])->result();

        //Menampilkan halaman
        $this->load->view('templates/header', $data);
        $this->load->view('artikel/repositorinew', $data);
        $this->load->view('templates/footer');
    }


    //FUNGSI UNTUK MENAMBAHKAN ARTIKEL
    public function tambah_data()
    {
        // mengosongkan folder file temporary
        delete_files('./assets/temp/');

        // limit parameter input tambah data
        $this->form_validation->set_rules('year', 'Year', 'required|max_length[4]');
        $this->form_validation->set_rules('volume', 'Volume', 'required|max_length[3]');
        $this->form_validation->set_rules('number', 'Number', 'required|max_length[14]');

        if ($this->form_validation->run() == false) {
            // jika form tambah data tidak terisi dengan benar
            $this->session->set_flashdata(
                //menampilkan pesan gagal menambahkan data
                'failure',
                '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Failed to add article.</strong> Please check the field requirements.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>'
            );
            redirect('Artikel/index');
        } else {
            // jika form tambah data terisi dengan benar
            $data['year']       = $this->input->post('year');
            $data['volume']     = $this->input->post('volume');
            $data['number']     = $this->input->post('number');


            //mengupload beberapa file sekaligus
            $countfiles        = count($_FILES['files']['name']);

            for ($i = 0; $i < $countfiles; $i++) {
                $_FILES['file']['name']     = $_FILES['files']['name'][$i];
                $_FILES['file']['type']     = $_FILES['files']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
                $_FILES['file']['error']    = $_FILES['files']['error'][$i];
                $_FILES['file']['size']     = $_FILES['files']['size'][$i];

                $data['file'] = $_FILES['file'];

                $this->Artikel_model->input_data($data['year'], $data['volume'], $data['number'], $data['file'], 'artikel');

                //menjalankan program python
                shell_exec("python C:/xampp/htdocs/pkl/application/controllers/python/formatDocxNew.py");
            }
        }


        //menampilkan pesan berhasil menambahkan dan memformat data
        $this->session->set_flashdata(
            'success',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Article formatted and added to the repository.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>'
        );

        redirect('Artikel/index');
    }

    //FUNGSI UNTUK MENGUNDUH FILE ARTIKEL
    public function unduh($namafile)
    {
        $this->load->helper('download');
        $data = file_get_contents('./assets/file/' . $namafile);
        force_download($namafile, $data);
    }


    //FUNGSI UNTUK MENGHAPUS ARTIKEL
    public function hapus($id)
    {
        $this->Artikel_model->hapus_data($id, 'artikel');

        //menampilkan pesan berhasil menghapus data
        $this->session->set_flashdata(
            'success',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Article deleted.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>'
        );
        redirect('Artikel/index');
    }
}
