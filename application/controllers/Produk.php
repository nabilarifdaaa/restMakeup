<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// Jika ada pesan "REST_Controller not found"
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Produk extends REST_Controller {

    // Konfigurasi letak folder untuk upload image
    private $folder_upload = 'uploads/';

    function produk_get(){
        $get_produk = $this->db->query("
            SELECT
               idProduk, namaProduk, jenisProduk, hargaProduk, detailProduk, photo_url
            FROM produk")->result();
       $this->response(
           array(
               "status" => "success",
               "result" => $get_produk
           )
       );
    }

    function produk_by_jenis_get(){
        $test = $this->get("jenisProduk");
        if (!empty($test)) {

        $get_produk =$this->db->query('
             SELECT 
               idProduk, namaProduk, jenisProduk, hargaProduk, detailProduk, photo_url
             FROM produk
             WHERE jenisProduk = "'.$test.'"')->result();
        $this->response(
           array(
               "status" => "success",
               "result" => $get_produk    
           )
       );
        } else {
            $this->response(
           array(
               "status" => "success",
               "result" => array()   
           ) );
        }

       //  $get_produk = $this->db->query("
       //      SELECT
       //         idProduk, namaProduk, jenisProduk, hargaProduk, detailProduk, photo_url
       //      FROM produk")->result();
       // $this->response(
       //     array(
       //         "status" => "success",
       //         "result" => $get_produk
       //     )
       // );
    }

    function produk_post() {
        $action  = $this->post('action');
        $data_produk = array(
                     'idProduk'     => $this->post('idProduk'),
                     'namaProduk'   => $this->post('namaProduk'),
                     'jenisProduk'  => $this->post('jenisProduk'),
                     'hargaProduk'  => $this->post('hargaProduk'),
                     'detailProduk' => $this->post('detailProduk'),
                     'photo_url'    => $this->post('photo_url')
                 );

        switch ($action) {
            case "insert" :
                $this->insertProduk($data_produk);
                break;
            
            case 'update':
                $this->updateProduk($data_produk);
                break;
            
            case 'delete':
                $this->deleteProduk($data_produk);
                break;


            
            default:
                $this->response(
                    array(
                        "status"  =>"failed",
                        "message" => "action harus diisi"
                    )
                );
                break;
        }
    }

    function insertProduk($data_produk){

     // Cek validasi
     if (empty($data_produk['namaProduk']) || empty($data_produk['jenisProduk']) || empty($data_produk['hargaProduk'])  || empty($data_produk['detailProduk']) ){
         $this->response(
             array(
                 "status" => "failed",
                 "message" => "Nama Produk / jenis / harga /detail harus diisi"
             )
         );
     } else {

         $data_produk['photo_url'] = $this->uploadPhoto();

         $do_insert = $this->db->insert('produk', $data_produk);
        
         if ($do_insert){
             $this->response(
                 array(
                     "status" => "success",
                     "result" => array($data_produk),
                     "message" => $do_insert
                 )
             );
            }
     }
    }

    function updateProduk($data_produk){

     // Cek validasi
     if (empty($data_produk['namaProduk']) || empty($data_produk['jenisProduk']) || empty($data_produk['hargaProduk'])  || empty($data_produk['detailProduk']) ){
        $this->response(
             array(
                 "status" => "failed",
                 "message" => "Nama Produk / jenis / harga /detail harus diisi"
             )
        );
     } else {
         // Cek apakah ada di database
         $get_produk_baseID = $this->db->query("
             SELECT 1
             FROM produk
             WHERE idProduk =  {$data_produk['idProduk']}")->num_rows();

         if($get_produk_baseID === 0){
             // Jika tidak ada
             $this->response(
                 array(
                     "status"  => "failed",
                     "message" => "ID Produk tidak ditemukan"
                 )
             );
         } else {
             // Jika ada
             $data_produk['photo_url'] = $this->uploadPhoto();

             if ($data_produk['photo_url']){
                 // Jika upload foto berhasil, eksekusi update
                 $update = $this->db->query("
                     UPDATE produk SET
                         namaProduk = '{$data_produk['namaProduk']}',
                         jenisProduk = '{$data_produk['jenisProduk']}',
                         hargaProduk = '{$data_produk['hargaProduk']}',
                         detailProduk = '{$data_produk['detailProduk']}',
                         photo_url = '{$data_produk['photo_url']}'
                     WHERE idProduk = '{$data_produk['idProduk']}'");

             } else {
                 // Jika foto kosong atau upload foto tidak berhasil, eksekusi update
                    $update = $this->db->query("
                        UPDATE produk
                        SET
                            namaProduk    = '{$data_produk['namaProduk']}',
                            jenisProduk  = '{$data_produk['jenisProduk']}',
                            hargaProduk    = '{$data_produk['hargaProduk']}',
                            detailProduk = '{$data_produk['detailProduk']}'
                        WHERE idProduk = {$data_produk['idProduk']}"
                    );
             }
            
             if ($update){
                 $this->response(
                     array(
                         "status"    => "success",
                         "result"    => array($data_produk),
                         "message"   => $update
                     )
                 );
                }
         }   
     }
    }

    function deleteProduk($data_produk){

        if (empty($data_produk['idProduk'])){
         $this->response(
             array(
                 "status" => "failed",
                 "message" => "ID Produk harus diisi"
             )
         );
     } else {
         // Cek apakah ada di database
         $get_produk_baseID =$this->db->query("
             SELECT 1
             FROM produk
             WHERE idProduk = {$data_produk['idProduk']}")->num_rows();

         if($get_produk_baseID > 0){
             
             $get_photo_url =$this->db->query("
             SELECT photo_url
             FROM produk
             WHERE idProduk = {$data_produk['idProduk']}")->result();
         
                if(!empty($get_photo_url)){

                    // Dapatkan nama file
                    $photo_nama_file = basename($get_photo_url[0]->photo_url);
                    // Dapatkan letak file di folder upload
                    $photo_lokasi_file = realpath(FCPATH . $this->folder_upload . $photo_nama_file);
                    
                    // Jika file ada, hapus
                    if(file_exists($photo_lokasi_file)) {
                        // Hapus file
                     unlink($photo_lokasi_file);
                 }

                 $this->db->query("
                     DELETE FROM produk
                     WHERE idProduk = {$data_produk['idProduk']}");
                 $this->response(
                     array(
                         "status" => "success",
                         "message" => "Data ID = " .$data_produk['idProduk']. " berhasil dihapus"
                     )
                 );
             }
         
            } else {
                $this->response(
                    array(
                        "status" => "failed",
                        "message" => "ID Produk tidak ditemukan"
                    )
                );
            }
     }
    }

    function uploadPhoto() {

        // Apakah user upload gambar?
        if ( isset($_FILES['photo_url']) && $_FILES['photo_url']['size'] > 0 ){

            // Foto disimpan di android-api/uploads
            $config['upload_path'] = realpath(FCPATH . $this->folder_upload);
            $config['allowed_types'] = 'jpg|png';

         // Load library upload & helper
         $this->load->library('upload', $config);
         $this->load->helper('url');

         // Apakah file berhasil diupload?
         if ( $this->upload->do_upload('photo_url')) {

               // Berhasil, simpan nama file-nya
               // URL image yang disimpan adalah http://localhost/android-api/uploads/namafile
             $img_data = $this->upload->data();
             //hilangkan baseurl
             $post_image = $this->folder_upload .$img_data['file_name'];

         } else {

             // Upload gagal, beri nama image dengan errornya
             // Ini bodoh, tapi efektif
             $post_image = $this->upload->display_errors();
             
         }
     } else {
         // Tidak ada file yang di-upload, kosongkan nama image-nya
         $post_image = '';
     }

     return $post_image;
    }
}
