<?php
use Restserver\libraries\REST_Controller;
require APPPATH . '/libraries/Format.php';
require APPPATH . '/libraries/REST_Controller.php';

class Rating extends REST_Controller {

   function rating_get() {
       $get_rating = $this->db->query("SELECT * FROM rating ")->result();
     
       $this->response(array("status"=>"success","result" => $get_rating));
   }

   function rating_post() {
       $data_rating = array(
           // 'idRating'   => $this->post('idRating'),
           'idProduk'   => $this->post('idProduk'),
           'idUser'     => $this->post('idUser'),
           'rating'     => $this->post('rating'),
           'review'     => $this->post('review'),
           'tanggal'    => $this->post('tanggal')
           );
      
       // // if  (empty($data_rating['idRating'])){
       // //      $this->response(array('status'=>'fail',"message"=>"idRating kosong"));
       // // }
       // // else {
       //     $getId = $this->db->query("SELECT idRating from rating where idRating='".$data_rating['idRating']."'")->result();
          
           // if (empty($getId)){
                    if (empty($data_rating['idProduk'])){
                       $this->response(array('status'=>'fail',"message"=>"idProduk kosong"));
                    }
                    else if(empty($data_rating['idUser'])){
                       $this->response(array('status'=>'fail',"message"=>"idUser kosong"));
                    }
                    else if(empty($data_rating['rating'])){
                       $this->response(array('status'=>'fail',"message"=>"rating kosong"));
                    }
                    else if(empty($data_rating['review'])){
                       $this->response(array('status'=>'fail',"message"=>"review kosong"));
                    }
                    else if(empty($data_rating['tanggal'])){
                       $this->response(array('status'=>'fail',"message"=>"tanggal kosong"));
                    }
                    else{
                       //GET ID USER
                       $getIdUser = $this->db->query("SELECT idUser from user Where idUser='".$data_rating['idUser']."'")->result();
                        //GET ID PRODUK
                       $getIdProduk = $this->db->query("SELECT idProduk from produk Where idProduk='".$data_rating['idProduk']."'")->result();

                       $message="";

                       if (empty($getIdUser)) $message.="idUser tidak ada/salah ";
                       if (empty($getIdProduk)) {
                           if (empty($message)) {
                               $message.="idProduk tidak ada/salah";
                           }
                           else {
                               $message.="dan idProduk tidak ada/salah";
                           }
                       }
                       if (empty($message)){
                           $insert= $this->db->insert('rating',$data_rating);
                           if ($insert){
                               $this->response(array('status'=>'success',
                                'result' => array($data_rating),"message"=>$insert));   
                           }
                          
                       }else{
                           $this->response(array('status'=>'fail',"message"=>$message));   
                       }
                      
                    }
           // } else{
           //     $this->response(array('status'=>'fail',"message"=>"idRating sudah ada"));
           // }  
       // }
   }

   // update data pembelian
   function rating_put() {
       $data_rating = array(
                   'idRating'   => $this->post('idRating'),
                   'idProduk'   => $this->post('idProduk'),
                   'idUser'     => $this->post('idUser'),
                   'rating'     => $this->post('rating'),
                   'review'     => $this->post('review'),
                   'tanggal'    => $this->post('tanggal')
                   );
      var_dump($this->post());
      die();
      if(empty($data_rating['idRating'])){
            $this->response(array('status'=>'fail',"message"=>"idRating kosong"));
      }
      else{
           $getId = $this->db->query("SELECT idRating from rating where idRating='".$data_rating['idRating']."'")->result();

           if (empty($getId)){
             $this->response(array('status'=>'fail',"message"=>"idRating tidak ada/salah")); 
           }else{
               if (empty($data_rating['idProduk'])){
                     $this->response(array('status'=>'fail',"message"=>"idProduk kosong"));
                  }
                  else if(empty($data_rating['idUser'])){
                     $this->response(array('status'=>'fail',"message"=>"idUser kosong"));
                  }
                  else if(empty($data_rating['rating'])){
                     $this->response(array('status'=>'fail',"message"=>"rating kosong"));
                  }
                  else if(empty($data_rating['review'])){
                     $this->response(array('status'=>'fail',"message"=>"review kosong"));
                  }
                  else if(empty($data_rating['tanggal'])){
                     $this->response(array('status'=>'fail',"message"=>"tanggal kosong"));
                  }
                  else{
                    //GET ID USER
                    $getIdUser = $this->db->query("SELECT idUser from user Where idUser='".$data_rating['idUser']."'")->result();
                    //GET ID PRODUK
                    $getIdProduk = $this->db->query("SELECT idProduk from produk Where idProduk='".$data_rating['idProduk']."'")->result();
                    
                    $message="";
                    
                   if (empty($getIdUser)) $message.="idUser tidak ada/salah ";
                   if (empty($getIdProduk)) {
                       if (empty($message)) {
                           $message.="idProduk tidak ada/salah";
                       }
                       else {
                           $message.="dan idProduk tidak ada/salah";
                       }
                   }
                   if (empty($message)){
                       $this->db->where('idRating',$data_rating['idRating']);
                       $update= $this->db->update('rating',$data_rating);
                       if ($update){
                           $this->response(array('status'=>'success','result' => $data_rating,"message"=>$update));
                       }
                      
                   }else{
                       $this->response(array('status'=>'fail',"message"=>$message));   
                   }
                }
           }

       }
   }

   function rating_delete() {
       $idRating = $this->delete('idRating');
       if (empty($idRating)){
           $this->response(array('status' => 'fail', "message"=>"idRating harus diisi"));
       } else {
           $this->db->where('idRating', $idRating);
           $delete = $this->db->delete('rating');  
           if ($this->db->affected_rows()) {
               $this->response(array('status' => 'success','message' =>"Berhasil delete dengan idRating = ".$idRating));
           } else {
               $this->response(array('status' => 'fail', 'message' =>"idRating tidak dalam database"));
           }
       }
   }
}  