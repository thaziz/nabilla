<?php

namespace App\Modules\POS\model;

use Illuminate\Database\Eloquent\Model;

use App\Modules\POS\model\d_sales_dt;

use App\Lib\mutasi;

use App\Lib\format;

use App\d_sales_payment;

use App\m_item;

use DB;

use Auth;

use Datatables;

use Session;

class d_sales extends Model
{  
    protected $table = 'd_sales';
    protected $primaryKey = 's_id';
    const CREATED_AT = 's_insert';
    const UPDATED_AT = 's_update';
    
      protected $fillable = ['s_id','s_comp','s_jenis_bayar','s_channel','s_machine','s_date','s_finishdate','s_duedate','s_note','s_create_by','s_customer','s_gross','s_disc_percent','s_disc_value','s_tax','s_ongkir','s_bulat','s_net','s_status','s_bayar','s_kembalian

      '];

      static function simpan($request){        
        return DB::transaction(function () use ($request) {                  
          $s_gross = format::format($request->s_gross);
          $s_ongkir = format::format($request->s_ongkir);          
          $s_disc_value = format::format($request->s_disc_value);
          $s_disc_percent = format::format($request->s_disc_percent);
          $s_net= format::format($request->s_net);
          $kembalian= format::format($request->kembalian);
          $bayar= format::format($request->s_bayar);
          $s_bulat= format::format($request->s_bulat);

          $s_id=d_sales::max('s_id')+1;             
          $note='TOKO-'.$s_id.'/'.date('Y.m.d');
          if($request->s_customer==''){
            $request->s_customer=0;
          }
          d_sales::create([
                    's_id' =>$s_id ,
                    's_comp'=>Session::get('user_comp'),
                    's_channel'=>'Toko',                    
                    's_date'=>date('Y-m-d',strtotime($request->s_date)),
                    's_note'=>$note,
                    's_machine'=>$request->s_machine,
                    's_create_by'=>Auth::user()->m_id,
                    's_customer'=>$request->s_customer,
                    's_gross' =>$s_gross,
                    's_disc_percent'=>$s_disc_percent,
                    's_disc_value'=>$s_disc_value,
                    's_tax'=>0,
                    's_ongkir'=>$s_ongkir,
                    's_net'=>$s_net,
                    's_bayar'=>$bayar,
                    's_status'=>$request->status,
                    /*'s_kembalian'=>$kembalian,*/
                    's_bulat'=>$s_bulat,
            ]);

  
          for ($i=0; $i <count($request->sd_item); $i++) {  
              if($request->status=='final'){
                  $sd_qty = format::format($request->sd_qty[$i]);
                  if(mutasi::mutasiStok($request->sd_item[$i],$sd_qty,$comp=1,$position=1,$flag='',$s_id)){
                  $sd_detailid=d_sales_dt::
                              where('sd_sales','=',$s_id)->max('sd_detailid')+1;      

                  $sd_price = format::format($request->sd_price[$i]);

                  $sd_total = format::format($request->sd_total[$i]);

                  $sd_disc_value = format::format($request->sd_disc_value[$i]);              

                  $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

                  d_sales_dt::create([
                            'sd_sales' =>$s_id ,
                            'sd_detailid'=>$sd_detailid,
                            'sd_date'    =>date('Y-m-d',strtotime($request->s_date)),
                            'sd_comp'=>Session::get('user_comp'),                    
                            'sd_item'=>$request->sd_item[$i],
                            'sd_qty'=>$sd_qty,                    
                            'sd_price' =>$sd_price,
                            'sd_disc_percent'=>$request->sd_disc_percent[$i],
                            'sd_disc_value'=>$sd_disc_value,
                            'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                            'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,
                  ]);
                }

            else{
              DB::rollBack();
               $data=['status'=>'gagal','data'=>'gagal'];
              return json_encode($data);
            }
          }else if($request->status=='draft'){
               $sd_detailid=d_sales_dt::
                              where('sd_sales','=',$s_id)->max('sd_detailid')+1;      

                  $sd_price = format::format($request->sd_price[$i]);

                  $sd_total = format::format($request->sd_total[$i]);

                  $sd_disc_value = format::format($request->sd_disc_value[$i]);              

                  $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

                  $sd_qty = format::format($request->sd_qty[$i]);

                  d_sales_dt::create([
                            'sd_sales' =>$s_id ,
                            'sd_detailid'=>$sd_detailid,   
                            'sd_date'    =>date('Y-m-d',strtotime($request->s_date)), 
                            'sd_comp'=>Session::get('user_comp'),                                    
                            'sd_item'=>$request->sd_item[$i],
                            'sd_qty'=>$sd_qty,                    
                            'sd_price' =>$sd_price,
                            'sd_disc_percent'=>$request->sd_disc_percent[$i],
                            'sd_disc_value'=>$sd_disc_value,
                            'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                            'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,
                  ]);

          }
        }
/*kembalian*/
          $totalBayar=0;
          $bayar=count($request->sp_nominal);
          for ($n=0; $n <$bayar; $n++) {  
            $jmlBayar=$bayar-1;            
            $sp_paymentid=d_sales_payment::
                          where('sp_sales','=',$s_id)->max('sp_paymentid')+1;  
            $sp_nominal = format::format($request->sp_nominal[$n]);    
            $s_kembalian = format::format($request->kembalian);
            if($jmlBayar==$n && $s_kembalian>0){              
              $sp_nominal=$sp_nominal-$s_kembalian;
            }            
              d_sales_payment::create([
                  'sp_sales'=>$s_id,
                  'sp_paymentid'=>$sp_paymentid,
                  'sp_comp'=>Session::get('user_comp'),                    
                  'sp_method'=>$request->sp_method[$n],
                  'sp_nominal'=>$sp_nominal,
                ]);
              $totalBayar+=$sp_nominal;

          }          
          $salesUpdate=d_sales::where('s_id',$s_id);
          $salesUpdate->update([
                  's_bayar'=>$totalBayar
            ]);
          $data=['status'=>'sukses','data'=>'sukses' ,'s_id'=>$s_id,'s_status'=>$request->status];
          return json_encode($data);
      });
    }
    static function perbarui ($request){
      
      return DB::transaction(function () use ($request) {           
        $updateSales=d_sales::where('s_id',$request->s_id);
          $permintaan=0;
          $s_gross = format::format($request->s_gross);
          $s_ongkir = format::format($request->s_ongkir);          
          $s_disc_value = format::format($request->s_disc_value);
          $s_disc_percent = format::format($request->s_disc_percent);
          $s_net= format::format($request->s_net);
          $kembalian= format::format($request->kembalian);
          $bayar= format::format($request->s_bayar);
          $s_bulat= format::format($request->s_bulat);

          if($request->s_customer==''){
            $request->s_customer=0;
          }
          $status=$updateSales->first()->s_status;
          $updateSales->update([
                    's_machine'=>$request->s_machine,
                    's_create_by'=>Auth::user()->m_id,
                    's_customer'=>$request->s_customer,
                    's_gross' =>$s_gross,
                    's_disc_percent'=>$s_disc_percent,
                    's_disc_value'=>$s_disc_value,                    
                    's_ongkir'=>$s_ongkir,
                    's_net'=>$s_net,
                    's_bayar'=>$bayar,
                    /*'s_kembalian'=>$kembalian,*/
                    's_bulat'=>$s_bulat,
                    's_status'=>'final',
                    ]);
          $hapusdt=[];
          if($request->hapusdt!=null){
            $hapusdt = explode(',',$request->hapusdt);
          }

            
        for ($h=0; $h <count($hapusdt) ; $h++) { 
                $hapusItem=$hapusdt[$h];
                $hapus_sales_dt=d_sales_dt::where('sd_sales',$request->s_id)                                  
                                  ->where('sd_item',$hapusItem);  
                if(count($hapus_sales_dt->first())!=0){
                  $permintaan=$hapus_sales_dt->first()->sd_qty;
                  
                  if($permintaan>0){
                      if($status=='final'){
                        if(mutasi::updateMutasi($hapusItem,-$permintaan,$comp=1,$position=1,$flag='',$request->s_id)){
                          $hapus_sales_dt->delete();
                        }
                        }else if($status='draft'){
                          $hapus_sales_dt->delete();
                        }
                      }
                    }
                }

        
        
       for ($i=0; $i <count($request->sd_item); $i++) {  
        //update

              if($request->sd_sales[$i]!=='' && $request->sd_detailid[$i]!=='' &&
                $request->sd_sales[$i]!==null && $request->sd_detailid[$i]!==null){  
                if($status=='final'){
                  
                  $permintaan=format::format($request->sd_qty[$i])-format::format($request->jumlahAwal[$i]);                  
                }else{
                  
                  $permintaan=format::format($request->sd_qty[$i]);
                }
              if(mutasi::updateMutasi($request->sd_item[$i],$permintaan,$comp=1,$position=1,$flag='',$request->sd_sales[$i])){

                $upadte_sales_dt=d_sales_dt::where('sd_sales',$request->sd_sales[$i])
                                  ->where('sd_detailid',$request->sd_detailid[$i])
                                  ->where('sd_item',$request->sd_item[$i]);
              $sd_price = format::format($request->sd_price[$i]);

              $sd_total = format::format($request->sd_total[$i]);

              $sd_disc_value = format::format($request->sd_disc_value[$i]);              

              $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

              $sd_qty = format::format($request->sd_qty[$i]);

                $upadte_sales_dt->update([
                                  'sd_qty'=>$sd_qty,
                                  'sd_price' =>$sd_price,
                                  'sd_disc_percent'=>$request->sd_disc_percent[$i],
                                  'sd_disc_value'=>$sd_disc_value,
                                  'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                                  'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,

                                  ]);

              }else{
                   $data=['status'=>'gagal','data'=>'gagal'];
                   DB::rollBack();
                  return json_encode($data);
                }    
         }else{
          
                $sd_qty=format::format($request->sd_qty[$i]);
              
                $s_id=$updateSales->first()->s_id;
                /*dd(mutasi::mutasiStok($request->sd_item[$i],$request->sd_qty[$i],$comp=1,$position=1,$flag='',$s_id));*/
                  if(mutasi::mutasiStok($request->sd_item[$i],$sd_qty,$comp=1,$position=1,$flag='',$s_id)){
                  $sd_detailid=d_sales_dt::
                              where('sd_sales','=',$s_id)->max('sd_detailid')+1;      

                  $sd_price = format::format($request->sd_price[$i]);

                  $sd_total = format::format($request->sd_total[$i]);

                  $sd_disc_value = format::format($request->sd_disc_value[$i]);              

                  $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

                  d_sales_dt::create([
                            'sd_sales' =>$s_id ,
                            'sd_detailid'=>$sd_detailid,     
                            'sd_date'    =>date('Y-m-d',strtotime($request->s_date)),               
                            'sd_comp'=>Session::get('user_comp'),                    
                            'sd_item'=>$request->sd_item[$i],
                            'sd_qty'=>$sd_qty,                    
                            'sd_price' =>$sd_price,
                            'sd_disc_percent'=>$request->sd_disc_percent[$i],
                            'sd_disc_value'=>$sd_disc_value,
                            'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                            'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,
                  ]);
                }

            else{
               $data=['status'=>'gagal','data'=>'gagal'];
               DB::rollBack();
              return json_encode($data);
            }          
        



         }
      }
        
      $deletePayment=d_sales_payment::
                     where('sp_sales','=', $updateSales->first()->s_id)->delete();


      $bayar=count($request->sp_nominal);
      $totalBayar=0;

      for ($n=0; $n <$bayar; $n++) {  
        $jmlBayar=$bayar-1;   
            $sp_paymentid=d_sales_payment::
                          where('sp_sales','=', $updateSales->first()->s_id)->max('sp_paymentid')+1;  
            $sp_nominal = format::format($request->sp_nominal[$n]);    

            $s_kembalian = format::format($request->kembalian);
            if($jmlBayar==$n){              
              $sp_nominal=$sp_nominal-$s_kembalian;              
            }            

              d_sales_payment::create([
                  'sp_sales'=> $updateSales->first()->s_id,
                  'sp_paymentid'=>$sp_paymentid,
                  'sp_comp'=>Session::get('user_comp'),                    
                  'sp_method'=>$request->sp_method[$n],
                  'sp_nominal'=>$sp_nominal,
                ]);
              $totalBayar+=$sp_nominal;
      } 

      
          $salesUpdate=d_sales::where('s_id',$request->s_id);
          $salesUpdate->update([
                  's_bayar'=>$totalBayar
            ]);



      $s_id=$updateSales->first()->s_id;
        $data=['status'=>'sukses','data'=>'sukses' ,'s_id'=>$s_id,'s_status'=>$updateSales->first()->s_status];
        return json_encode($data);
    });
    }
    static function listPenjualanData($request){      
      $from=date('Y-m-d',strtotime($request->tanggal1));
      $to=date('Y-m-d',strtotime($request->tanggal2));

             
      $d_sales = DB::table('d_sales')->leftJoin('m_customer','s_customer','=','c_id')->where('s_channel',$request->type)
                 ->whereBetween('s_date', [$from, $to])->where('s_comp',Session::get('user_comp'))->get();
      
        
          return Datatables::of($d_sales)
                         ->addColumn('item', function ($d_sales) {
                            return'<button onclick=dataDetailView(
                                                '.$d_sales->s_id.',
                                                \''.$d_sales->s_note.'\',
                                                \''.$d_sales->s_machine.'\',
                                                
                                                \''.date('d-m-Y',strtotime($d_sales->s_date)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_duedate)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_finishdate)).'\',
                                                \''.number_format($d_sales->s_gross,0,',','.').'\',
                                                '.$d_sales->s_disc_percent.',
                                                '.$d_sales->s_disc_value.',
                                                \''.number_format($d_sales->s_gross-$d_sales->s_disc_percent-$d_sales->s_disc_value,0,',','.').'\',
                                                \''.number_format($d_sales->s_ongkir,0,',','.').'\',
                                                \''.number_format($d_sales->s_bulat,0,',','.').'\',
                                                \''.number_format($d_sales->s_net,0,',','.').'\',
                                                \''.number_format($d_sales->s_bayar,0,',','.').'\',
                                                \''.number_format($d_sales->s_kembalian,0,',','.').'\',
                                                \''.$d_sales->s_customer.'\',
                                                \''.$d_sales->c_name.'\',
                                                \''.$d_sales->s_status.'\',                                                
                                                '.($d_sales->s_net-$d_sales->s_bayar).',
                                                \''.$d_sales->s_jenis_bayar.'\',
                            ) class="btn btn-outlined btn-info btn-xs" type="button"        data-target="#detail" data-toggle="modal">Detail</button>';
                        })                         
                      ->editColumn('s_status', function ($d_sales) {
                            if ($d_sales->s_status == 'draft')
                                return '<span class="label label-warning">Draft</span>';
                            if ($d_sales->s_status == 'final')
                                return '<span class="label label-success">Final</span>';
                            if ($d_sales->s_status == 'lunas')
                                return '<span class="label label-default">Lunas</span>';
                        })
                      ->editColumn('s_date', function ($d_sales) {                            
                                return date('d-m-Y',strtotime($d_sales->s_date));                            
                        })
                      ->editColumn('s_gross', function ($d_sales) {                            
                                return number_format($d_sales->s_gross,0,',','.');
                        })
                      ->editColumn('s_ongkir', function ($d_sales) {                            
                                return number_format($d_sales->s_ongkir,0,',','.');
                        })
                      ->editColumn('s_net', function ($d_sales) {                            
                                return number_format($d_sales->s_net,0,',','.');
                        })
                      ->editColumn('s_disc_percent', function ($d_sales) {                            
                                return number_format($d_sales->s_disc_percent+$d_sales->s_disc_value,0,',','.');
                        })
                         ->addColumn('action', function ($d_sales) {
                            $disable='';
                            if($d_sales->s_status=='final' && $d_sales->s_channel=='Toko' ){
                              $disable='disabled';
                            }
                            if($d_sales->s_status=='lunas' && $d_sales->s_channel=='Pesanan' ){
                              $disable='disabled';
                            }

                            $html='';  

                          $html.='<div class="text-center">
                          <button type="button" class="btn btn-xs btn-success" title="Detail" onclick="dataDetailView(
                                                '.$d_sales->s_id.',
                                                \''.$d_sales->s_note.'\',
                                                \''.$d_sales->s_machine.'\',
                                                
                                                \''.date('d-m-Y',strtotime($d_sales->s_date)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_duedate)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_finishdate)).'\',
                                                \''.number_format($d_sales->s_gross,0,',','.').'\',
                                                '.$d_sales->s_disc_percent.',
                                                '.$d_sales->s_disc_value.',
                                                \''.number_format($d_sales->s_gross-$d_sales->s_disc_percent-$d_sales->s_disc_value,0,',','.').'\',
                                                \''.number_format($d_sales->s_ongkir,0,',','.').'\',
                                                \''.number_format($d_sales->s_bulat,0,',','.').'\',
                                                \''.number_format($d_sales->s_net,0,',','.').'\',
                                                \''.number_format($d_sales->s_bayar,0,',','.').'\',
                                                \''.number_format($d_sales->s_kembalian,0,',','.').'\',
                                                \''.$d_sales->s_customer.'\',
                                                \''.$d_sales->c_name.'\',
                                                \''.$d_sales->s_status.'\',                                                
                                                '.($d_sales->s_net-$d_sales->s_bayar).',
                                                \''.$d_sales->s_jenis_bayar.'\',
                                                )" ><i class="fa fa-eye"></i> 
                          </button>
                          <button type="button" class="btn btn-xs btn-warning" title="Edit"onclick="editPenjualan(
                                                '.$d_sales->s_id.',
                                                \''.$d_sales->s_note.'\',
                                                \''.$d_sales->s_machine.'\',
                                                
                                                \''.date('d-m-Y',strtotime($d_sales->s_date)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_duedate)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_finishdate)).'\',
                                                \''.number_format($d_sales->s_gross,0,',','.').'\',
                                                '.$d_sales->s_disc_percent.',
                                                '.$d_sales->s_disc_value.',
                                                \''.number_format($d_sales->s_gross-$d_sales->s_disc_percent-$d_sales->s_disc_value,0,',','.').'\',
                                                \''.number_format($d_sales->s_ongkir,0,',','.').'\',
                                                \''.number_format($d_sales->s_bulat,0,',','.').'\',
                                                \''.number_format($d_sales->s_net,0,',','.').'\',
                                                \''.number_format($d_sales->s_bayar,0,',','.').'\',
                                                \''.number_format($d_sales->s_kembalian,0,',','.').'\',
                                                \''.$d_sales->s_customer.'\',
                                                \''.$d_sales->c_name.'\',
                                                \''.$d_sales->s_status.'\',                                                
                                                '.($d_sales->s_net-$d_sales->s_bayar).',
                                                \''.$d_sales->s_jenis_bayar.'\',
                                                )" '.$disable.' ><i class="fa fa-edit"></i>
                          </button>
                          <button type="button" class="btn btn-xs btn-danger" title="Hapus" onclick="deleteProduksi(
                          '.$d_sales->s_id.'
                          )" '.$disable.'><i class="fa fa-times"></i>
                          </button>
                          </div>';

                            return $html;
                        })





















/*

                        ->addColumn('action', function ($d_sales) {
                            $html='';                            
                                $html.=' <div class="dropdown">                                
                                            <button class="btn btn-primary btn-flat btn-xs dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                Kelola
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">';
if($d_sales->s_status=='lunas'){ 
 $html.='<li><a><i class="fa fa-pencil" aria-hidden="true"></i>-</a></li>';   

}
if($d_sales->s_status!='lunas'){  
                                $html.='<li><a onclick="editPenjualan(
                                                '.$d_sales->s_id.',
                                                \''.$d_sales->s_note.'\',
                                                \''.$d_sales->s_machine.'\',
                                                
                                                \''.date('d-m-Y',strtotime($d_sales->s_date)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_duedate)).'\',
                                                \''.date('d-m-Y',strtotime($d_sales->s_finishdate)).'\',
                                                \''.number_format($d_sales->s_gross,0,',','.').'\',
                                                '.$d_sales->s_disc_percent.',
                                                '.$d_sales->s_disc_value.',
                                                \''.number_format($d_sales->s_gross-$d_sales->s_disc_percent-$d_sales->s_disc_value,0,',','.').'\',
                                                \''.number_format($d_sales->s_ongkir,0,',','.').'\',
                                                \''.number_format($d_sales->s_bulat,0,',','.').'\',
                                                \''.number_format($d_sales->s_net,0,',','.').'\',
                                                \''.number_format($d_sales->s_bayar,0,',','.').'\',
                                                \''.number_format($d_sales->s_kembalian,0,',','.').'\',
                                                \''.$d_sales->s_customer.'\',
                                                \''.$d_sales->c_name.'\',
                                                \''.$d_sales->s_status.'\',                                                
                                                '.($d_sales->s_net-$d_sales->s_bayar).',
                                                \''.$d_sales->s_jenis_bayar.'\',
                                                )" ><i class="fa fa-pencil" aria-hidden="true"></i>Edit Data</a></li>';                                            
}
                                                 
                                                
                             $html.=    '</ul>                                            
                                    </div>';
                            return $html;
                        })*/
                        ->rawColumns(['item','action','s_status'])
                        ->make(true);            
    }
    /*<li role="separator" class="divider"></li>                                                                        
                                                <li><a class="btn-delete" onclick="hapusPenjualan('.$d_sales->s_id.')"></i>Hapus Data</a></li>                                                */

    static function printNota($s_id){
          $sales=DB::table('d_sales')->leftJoin('m_customer','s_customer','=','c_id')
                 ->where('s_id',$s_id)->first();
          $sales_dt=DB::table('d_sales_dt')
                    ->join('m_item','sd_item','=','i_id')
                    ->join('m_satuan','s_id','=','i_satuan')
                    ->where('sd_sales',$s_id)
                    ->get();
          $data['sales']=$sales;
          $data['sales_dt']=$sales_dt;
          return $data;

    }







      static function simpanPesanan($request){        
        return DB::transaction(function () use ($request) {   
          if($request->s_customer=="0" || $request->s_customer==""){
            $data=['status'=>'gagal','data'=>'Nama pelanggan harus di isi'];
            return $data;
          }
          
          $s_gross = format::format($request->s_gross);
          $s_ongkir = format::format($request->s_ongkir);          
          $s_disc_value = format::format($request->s_disc_value);
          $s_disc_percent = format::format($request->s_disc_percent);
          $s_net= format::format($request->s_net);
           $kembalian= format::format($request->kembalian);
          $bayar= format::format($request->s_bayar);
          $s_bulat= format::format($request->s_bulat);

          $s_id=d_sales::max('s_id')+1;             
          $note='PESANAN-'.$s_id.'/'.date('Y.m.d');
          if($request->s_customer==''){
            $request->s_customer=0;
          }
          
          d_sales::create([
                    's_id' =>$s_id ,
                    's_comp'=>Session::get('user_comp'),                    
                    's_channel'=>'Pesanan',
                    's_jenis_bayar'=>$request->s_jenis_bayar,
                    's_date'=>date('Y-m-d',strtotime($request->s_date)),
                    's_duedate'=>date('Y-m-d',strtotime($request->s_duedate)),
                    's_finishdate'=>date('Y-m-d',strtotime($request->s_finishdate)),
                    's_note'=>$note,
                    's_machine'=>$request->s_machine,
                    's_create_by'=>Auth::user()->m_id,
                    's_customer'=>$request->s_customer,
                    's_gross' =>$s_gross,
                    's_disc_percent'=>$s_disc_percent,
                    's_disc_value'=>$s_disc_value,
                    's_tax'=>0,
                    's_ongkir'=>$s_ongkir,
                    's_net'=>$s_net,
                    's_status'=>$request->status,
                    's_bayar'=>$bayar,
                    /*'s_kembalian'=>$kembalian,*/
                    's_bulat'=>$s_bulat
            ]);

  
          for ($i=0; $i <count($request->sd_item); $i++) {  
        
               $sd_detailid=d_sales_dt::
                              where('sd_sales','=',$s_id)->max('sd_detailid')+1;      

                  $sd_price = format::format($request->sd_price[$i]);

                  $sd_total = format::format($request->sd_total[$i]);

                  $sd_disc_value = format::format($request->sd_disc_value[$i]);              

                  $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

                  $sd_qty= format::format($request->sd_qty[$i]);

                  d_sales_dt::create([
                            'sd_sales' =>$s_id ,
                            'sd_detailid'=>$sd_detailid,   
                            'sd_date'    =>date('Y-m-d',strtotime($request->s_date)), 
                            'sd_comp'=>Session::get('user_comp'),                                    
                            'sd_item'=>$request->sd_item[$i],
                            'sd_qty'=>$sd_qty,                    
                            'sd_price' =>$sd_price,
                            'sd_disc_percent'=>$request->sd_disc_percent[$i],
                            'sd_disc_value'=>$sd_disc_value,
                            'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                            'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,
                  ]);

          
        }

/*dd($request->all());*/
$bayar=count($request->sp_nominal);
$totalBayar=0;
          for ($n=0; $n <$bayar; $n++) {  
            $jmlBayar=$bayar-1;   
            $sp_paymentid=d_sales_payment::
                          where('sp_sales','=',$s_id)->max('sp_paymentid')+1;  
            $sp_nominal = format::format($request->sp_nominal[$n]);    
            $s_kembalian = format::format($request->kembalian);
            if($jmlBayar==$n && $s_kembalian>0){              
              $sp_nominal=$sp_nominal-$s_kembalian;
            }            
              d_sales_payment::create([
                  'sp_sales'=>$s_id,
                  'sp_paymentid'=>$sp_paymentid,
                  'sp_comp'=>Session::get('user_comp'),                    
                  'sp_method'=>$request->sp_method[$n],
                  'sp_nominal'=>$sp_nominal,
                ]);

          $totalBayar+=$sp_nominal;
        } 

      
          $salesUpdate=d_sales::where('s_id',$s_id);
          $salesUpdate->update([
                  's_bayar'=>$totalBayar
            ]); 
          $data=['status'=>'sukses','data'=>'sukses' ,'s_id'=>$s_id,'s_status'=>$request->status];
          return json_encode($data);
      });
    }





     static function perbaruiPesanan ($request){
      
      return DB::transaction(function () use ($request) {           
         $updateSales=d_sales::where('s_id',$request->s_id);
         /*dd($request->all());*/
/*dd($request->all());*/
          $s_gross = format::format($request->s_gross);
          $s_ongkir = format::format($request->s_ongkir);          
          $s_disc_value = format::format($request->s_disc_value);
          $s_disc_percent = format::format($request->s_disc_percent);
          $s_net= format::format($request->s_net);
          $kembalian= format::format($request->kembalian);
          $bayar= format::format($request->s_bayar);
          $s_bulat= format::format($request->s_bulat);
          $status=$updateSales->first()->s_status;
          $updateSales->update([
                    /*'s_machine'=>$request->s_machine,*/
                    's_create_by'=>Auth::user()->m_id,
                    /*'s_customer'=>$request->s_customer,*/
                    's_gross' =>$s_gross,
                    's_disc_percent'=>$s_disc_percent,
                    's_disc_value'=>$s_disc_value,                    
                    's_ongkir'=>$s_ongkir,
                    's_net'=>$s_net,
                    's_bayar'=>$bayar,
                    /*'s_kembalian'=>$kembalian,*/
                    's_bulat'=>$s_bulat,
                    's_status'=>'final',
                    ]);

       for ($i=0; $i <count($request->sd_item); $i++) {  
        //update
              if($request->sd_sales[$i]!=='' && $request->sd_detailid[$i]!=='' &&
                $request->sd_sales[$i]!==null && $request->sd_detailid[$i]!==null){  
               

                $upadte_sales_dt=d_sales_dt::where('sd_sales',$request->sd_sales[$i])
                                  ->where('sd_detailid',$request->sd_detailid[$i])
                                  ->where('sd_item',$request->sd_item[$i]);
              $sd_price = format::format($request->sd_price[$i]);

              $sd_total = format::format($request->sd_total[$i]);

              $sd_disc_value = format::format($request->sd_disc_value[$i]);              

              $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

              $sd_qty= format::format($request->sd_qty[$i]);

                $upadte_sales_dt->update([
                                  'sd_qty'=>$sd_qty,
                                  'sd_price' =>$sd_price,
                                  'sd_disc_percent'=>$request->sd_disc_percent[$i],
                                  'sd_disc_value'=>$sd_disc_value,
                                  'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                                  'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,

                                  ]);
         }else{
          

              
                $s_id=$updateSales->first()->s_id;

                $sd_detailid=d_sales_dt::
                              where('sd_sales','=',$s_id)->max('sd_detailid')+1;      

                  $sd_price = format::format($request->sd_price[$i]);

                  $sd_total = format::format($request->sd_total[$i]);

                  $sd_disc_value = format::format($request->sd_disc_value[$i]);              

                  $sd_disc_percentvalue = format::format($request->sd_disc_percentvalue[$i]);

                  $sd_qty= format::format($request->sd_qty[$i]);

                  d_sales_dt::create([
                            'sd_sales' =>$s_id ,
                            'sd_detailid'=>$sd_detailid, 
                            'sd_date'    =>date('Y-m-d',strtotime($request->s_date)),
                            'sd_comp'=>Session::get('user_comp'),                                       
                            'sd_item'=>$request->sd_item[$i],
                            'sd_qty'=>$sd_qty,                    
                            'sd_price' =>$sd_price,
                            'sd_disc_percent'=>$request->sd_disc_percent[$i],
                            'sd_disc_value'=>$sd_disc_value,
                            'sd_disc_percentvalue'=>$sd_disc_percentvalue,
                            'sd_total'=>$sd_total-$sd_disc_value-$sd_disc_percentvalue,
                  ]);
                   
        
         }
      }
        

      $deletePayment=d_sales_payment::
                     where('sp_sales','=', $updateSales->first()->s_id)->delete();

$bayar=count($request->sp_nominal);            
$totalBayar=0;
      for ($n=0; $n <$bayar; $n++) {  
        $jmlBayar=$bayar-1;   
            $sp_paymentid=d_sales_payment::
                          where('sp_sales','=', $updateSales->first()->s_id)->max('sp_paymentid')+1;  
            $sp_nominal = format::format($request->sp_nominal[$n]);    
            $s_kembalian = format::format($request->kembalian);
            if($jmlBayar==$n && $s_kembalian>0){              
              $sp_nominal=$sp_nominal-$s_kembalian;
            }            

              d_sales_payment::create([
                  'sp_sales'=> $updateSales->first()->s_id,
                  'sp_paymentid'=>$sp_paymentid,
                  'sp_comp'=>Session::get('user_comp'),                    
                  'sp_method'=>$request->sp_method[$n],
                  'sp_nominal'=>$sp_nominal,
                ]);

      $totalBayar+=$sp_nominal;
      } 

      
          $salesUpdate=d_sales::where('s_id',$request->s_id);
          $salesUpdate->update([
                  's_bayar'=>$totalBayar
            ]);



      $s_id=$updateSales->first()->s_id;
        $data=['status'=>'sukses','data'=>'sukses' ,'s_id'=>$s_id,'s_status'=>$updateSales->first()->s_status];
        return json_encode($data);
    });
    }

    static function serahTerima($request){      
      return DB::transaction(function () use ($request) {

          $updateSales=d_sales::where('s_id',$request->s_id);  
          $status=$updateSales->first()->s_status;
          $updateSales->update([                    
                    's_status'=>'lunas',
                    ]);       
        for ($i=0; $i <count($request->sd_item); $i++) { 
            if(mutasi::mutasiStok($request->sd_item[$i],$request->sd_qty[$i],$comp=1,$position=1,$flag='',$request->s_id)){

            }else{
              $data=['status'=>'gagal','data'=>'Stok tidak mencukupi.' ,'s_id'=>$request->s_id,'s_status'=>$updateSales->first()->s_status];
              DB::rollBack();     
              return $data;      
            }
        }

        $data=['status'=>'sukses','data'=>'Stok berhasil disimpan.' ,'s_id'=>$request->s_id,'s_status'=>$updateSales->first()->s_status];
        return $data;
      });
    }
}
	
	