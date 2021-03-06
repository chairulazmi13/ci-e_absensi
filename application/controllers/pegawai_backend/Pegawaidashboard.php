<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawaidashboard extends CI_Controller {
  function __construct(){
  	parent::__construct();

  	if($this->session->userdata('pegawai_login') == 0){
  		redirect(base_url('pegawai-login'));
  	}

    $this->load->model('Mpegawai');
    $this->load->model('Mabsensi');
    $this->load->library('hitunghari');
  }

  function index()
  {
  	$this->load->view('template/header_pegawai');
    $this->load->view('pegawai/dashboard');
    $this->load->view('template/footer_pegawai');
  }

  function appsview()
  {
    $this->load->view('template/header_pegawai_x');
    $this->load->view('pegawai/dashboard');
    $this->load->view('template/footer_pegawai');
  }

  function gerateQrCode()
  {
        $id_pegawai = $this->input->post('id_pegawai');
        $nip = $this->session->userdata('p_nip');
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $ip2long = ip2long($ip_address);

        // mengkofersi ip address ke long integer
        $qrcode = sprintf("%s%d",$nip,$ip2long);

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE

        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/qrcode/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);

        $image_name=$nip.'.png'; //buat name dari qr code sesuai dengan nim

        $params['data'] = $qrcode; //data yang akan di jadikan QR CODE
        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

        $this->load->helper("file");
        $path = $config['imagedir'].$image_name;
        delete_files($path);
        $this->Mpegawai->generateQR($id_pegawai,$image_name); //simpan ke database

        $response['img'] = $path;
        $response['msg'] = 'generate QR Code success !';

        echo json_encode($response);
  }

  function getIndexPerBulan()
  {
    $id_pegawai = $this->session->userdata('p_id_pegawai');
    $bulan = date('m');
    $tahun = date('Y');
    // Untuk memnukan tanggal akhir bulan dengan menghitung total hari perbulan
    $hari = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);

    $start = $this->hitunghari->tglindo(date('Y-m-01'));
    $end   = $this->hitunghari->tglindo(date('Y-m-'.$hari));
    $harikerja = $this->hitunghari->hitungHariKerja($start,$end,"-");

    $data = $this->Mabsensi->indexKehadiran($bulan,$harikerja,$id_pegawai);

    foreach ($data->result_array() as $hasil) {
      $response[] = $hasil;
    }

    echo json_encode($response);
  }

}
