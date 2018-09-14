    <div class="row">
   
      <div class="col-md-12 col-sm-12 col-xs-12">
        

            
              <div class="col-md-2 col-sm-3 col-xs-12">
                <label class="tebal">Tanggal</label>
              </div>

              <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                  <div class="input-daterange input-group">
                    <input id="tanggal1" class="form-control input-sm datepicker2" name="tanggal1" type="text">
                    <span class="input-group-addon">-</span>
                    <input id="tanggal2"" class="input-sm form-control datepicker2" name="tanggal2" type="text">
                  </div>
                </div>
              </div>
            

              <div class="col-md-3 col-sm-6 col-xs-12" align="center">
                <button class="btn btn-primary btn-sm btn-flat" title="Cari rentang tanggal" type="button" onclick="cari()">
                  <strong>
                    <i class="fa fa-search" aria-hidden="true"></i>
                  </strong>
                </button>
                <button class="btn btn-info btn-sm btn-flat" type="button" title="Reset" onclick="resetData()">
                  <strong>
                    <i class="fa fa-undo" aria-hidden="true"></i>
                  </strong>
                </button>
                <button class="btn btn-default btn-sm btn-flat" type="button" title="cetak laporan penjualan" onclick="cetakData()">
                  <strong>
                    <i class="fa fa-print" aria-hidden="true"></i>
                  </strong>
                </button>
              </div>

              <div class="col-md-3 col-sm-6 col-xs-12" align="right">
                  <button type="button" class="btn btn-xs btn-primary btn-disabled btn-flat" onclick="tambah()">
                        <i class="fa fa fa-plus"></i> &nbsp;&nbsp;Tambah Data
                  </button>
              </div>
        


      </div>
  </div>

           
            <div class="table-responsive">
              <table class="table table-stripped tabelan table-bordered table-hover dt-responsive data-table tableListToko" id="tableListToko" width="100%">
               <thead align="right">
                <tr>
                 <th width="10%">Tanggal</th>
                 <th width="20%">No Nota</th>
                 <th width="20%">Pelangan</th>
                 <th width="10%">Kasir</th>  
                 <!-- <th width="10%">Item</th>      -->
                 <th width="10%">Gross</th>     
                 <th width="10%">Disc</th>                      
                 <th width="10%">Ongkir</th>     
                 <th width="10%">Net</th>                        
                 <th width="10%">Status</th> 
                 <th width="10%">Action</th>
                </tr>
               </thead> 
               <tbody>                
               </tbody>
              </table>
            </div>



           <div class="modal fade" id="modalDataDetail" role="dialog">
                    <div class="modal-dialog modal-lg">
                        
                    
                        <!-- Modal content-->
                          <div class="modal-content">
                            <div class="modal-header" style="background-color: #e77c38;">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title" style="color: white;">Detail Penjualan</h4>
                            </div>

                            <div class="modal-body" style="padding:0px">
                              
                              <div class="col-md-12 col-sm-12 col-xs-12 tamma-bg" >
                                


<label class="tebal">Status : </label>&nbsp;&nbsp;
          <span class="" id="txt_span_status"></span>
          <div class="col-md-12 col-sm-12 col-xs-12 tamma-bg" style="margin-top:10px;padding-bottom: 10px;padding-top: 20px;margin-bottom: 15px;">                          
            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Tanggal</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lTgl"></label>
              </div>  
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">No. Nota</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lCode"></label>
              </div>  
            </div>
            
            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Nama Pelanggan</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lCustomer"></label>
              </div>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Pembayaran</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lBayar"></label>
              </div>
            </div>


            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Jatuh Tempo</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lTempo"></label>
              </div>
            </div>


            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Tanggal Jadi</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lJadi"></label>
              </div>
            </div>

          </div>






                              
                                
<div class="table-responsive">
              <table class="table tabelan table-bordered table-hover dt-responsive">
               <thead align="right">
                <tr>                 
                 <th width="25%">Nama</th>
                 <th width="4%">Jumlah</th>
                 <th width="5%">Satuan</th>
                 <th width="6%">Harga</th>
                 <th width="4%">Disc(Rp.)</th>
                 <th width="3%">Disc(%)</th>
                 <th width="10%">Total</th>                 
                 
                </tr>
               </thead> 
               <tbody class="dataDetail">
               </tbody>
              </table>
</div>






<div class="col-md-12 col-sm-12 col-xs-12 tamma-bg" style="margin-top:10px;padding-bottom: 10px;padding-top: 20px;margin-bottom: 15px;">                          
       

            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Sub Total</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lSubttl"></label>
              </div>  
            </div>
           

            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Total Diskon</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lDiskon"></label>
              </div>
            </div>


          


            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Biaya Kirim</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lBkirim"></label>
              </div>
            </div>

          


            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Grand Total + Biaya Kirim</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lTtl"></label>
              </div>
            </div>



         

            <div class="col-md-3 col-sm-12 col-xs-12">
              <label class="tebal">Total Bayar</label>
            </div>

            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="form-group">
                <label id="lBiaya"></label>
              </div>
            </div>



          </div>



                              
                             

                                          
                              </div>
                            </div>
                        
                            <div class="modal-footer">
                              <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          
                      </div>
                  </div>
                <!-- End Modal Proses -->
          <script type="text/javascript">
dateAwal();
function dateAwal(){
      var d = new Date();
      d.setDate(d.getDate()-7);

      /*d.toLocaleString();*/
      $('#tanggal1').datepicker({
            format:"dd-mm-yyyy",        
            autoclose: true,
      }).datepicker( "setDate", d);
      $('#tanggal2').datepicker({
            format:"dd-mm-yyyy",        
            autoclose: true,
      }).datepicker( "setDate", new Date());
}


function resetData(){  
  dateAwal();
  table();
}
                                                
    function editPenjualan(s_id,s_note,s_machine,s_date,s_duedate,s_finishdate,s_gross,s_disc_percent,s_disc_value,s_grand,s_ongkir,s_bulat,s_net,s_bayar,s_kembalian,s_customer,c_name,s_status,chek,s_jenis_bayar) {

            $('.reset').val('');
            $('#s_created_by').val('{{Auth::user()->m_name}}')
            $('#s_id').val(s_id);
            $('#s_status').val(s_status);
            $('#s_date').val(s_date);
            $('#s_note').val(s_note);            
            $('#s_machine').val(s_machine);
            $('#s_gross').val(s_gross);        
            $('#s_date').val(s_date);  
            $('#s_duedate').val(s_duedate);  
            $('#s_finishdate').val(s_finishdate);  
            if(s_disc_value!='' && s_disc_value!=0 ){
              s_disc_value=SetFormRupiah(s_disc_value)
            }
            $('#discountRp').val(s_disc_value);
            if(s_disc_percent!='' && s_disc_percent!=0 ){
              s_disc_percent=SetFormRupiah(s_disc_percent)
            }
            $('#discountP').val(s_disc_percent);
            $('#discount').val(SetFormRupiah(s_disc_value+s_disc_percent));
            
            $('#vbiaya_kirim').val(s_ongkir);            
            $('#biaya_kirim').val(s_ongkir);            
            $('#grand').val(s_grand);
            $('#grand_biaya').val(s_net);
            $('#s_bulat').val(s_bulat);
            $('#akumulasiTotal').val(s_net);
            $('#penjualan').tab('show');
            $('#customer').val(c_name)
            $('#s_customer').val(s_customer)
            $('#totalBayar').val(s_bayar)
            $('#kembalian').val(s_kembalian)

                if(parseInt(chek)>0){
                $('.perbarui').css('display','');
                $('.terima').css('display','none');
            }
            if(parseInt(chek)<0){
              $('.perbarui').css('display','none');
              $('.terima').css('display','');

            }
            if(s_net==s_bayar && s_status=='draft' || s_net==s_bayar && s_status=='final'){              
              $('#serah_terima').removeAttr('disabled');
            }else if (s_net!=s_bayar && s_status=='draft' || s_net!=s_bayar && s_status=='final'){              
              $('#serah_terima').attr('disabled','disabled');
            }else if(s_status=='lunas'){
              $('#serah_terima').attr('disabled','disabled');
            }
            if(s_jenis_bayar==1){              
              $("#s_jenis_bayar").val(s_jenis_bayar).change();
              $("#s_jenis_bayar").attr('disabled','disabled');                            
            }
            if(s_jenis_bayar==2){              
              $("#s_jenis_bayar").val(s_jenis_bayar).change();
              $("#s_jenis_bayar").attr('disabled','disabled');                            
            }
            disabled();
            detail(s_id);

    }



          </script>