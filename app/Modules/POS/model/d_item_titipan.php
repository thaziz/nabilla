<?php

namespace App\Modules\POS\model;

use Illuminate\Database\Eloquent\Model;

use DB;

use Datatables;

use App\Lib\format;

use App\Lib\mutasi;

use Session;

class d_item_titipan extends Model
{  



    protected $table = 'd_item_titipan';
    protected $primaryKey = 'it_id';
    const CREATED_AT = 'it_created';
    const UPDATED_AT = 'it_updated';

    protected $fillable = ['it_id','it_comp','it_code','it_supplier','it_date','it_toko','it_note','it_total','it_keterangan','it_status','it_bayar','it_disc'];

    static function itemTitipan($request){
      $from=date('Y-m-d',strtotime($request->tanggal1));
      $to=date('Y-m-d',strtotime($request->tanggal2));
      

             $itemTitipan=d_item_titipan::join('m_supplier','s_id','=','it_supplier')->whereBetween('it_date', [$from, $to])->where('it_comp',Session::get('user_comp'))->get();
             return Datatables::of($itemTitipan)                                                
                        ->editColumn('it_date', function ($itemTitipan) {                            
                                return date('d-m-Y',strtotime($itemTitipan->it_date));                            
                        })    
                        ->editColumn('it_total', function ($itemTitipan) {                            
                                return number_format($itemTitipan->it_total,'0',',','.');                            
                        })                                       
                        ->addColumn('action', function ($itemTitipan) {  
                          $html='';  
                          $html.='<div class="text-center">
                          <button type="button" class="btn btn-sm btn-primary" title="Serah Terima" onclick="serahterima(
                                               '.$itemTitipan->it_id.',          
                                                \''.date('d-m-Y',strtotime($itemTitipan->it_date)).'\',   
                                                \''.$itemTitipan->it_code.'\',
                                                \''.$itemTitipan->it_keterangan.'\'  
                          )" ><i class="fa fa-times"></i>
                          </button>
                          <button class="btn btn-sm btn-success" title="Detail" onclick="detailMutasi(
                                                '.$itemTitipan->it_id.',          
                                                \''.date('d-m-Y',strtotime($itemTitipan->it_date)).'\',   
                                                \''.$itemTitipan->it_code.'\',
                                                \''.$itemTitipan->it_keterangan.'\'                                                
                          )"><i class="fa fa-eye"></i> 
                          </button>
                          <button class="btn btn-sm btn-warning" title="Edit" onclick="editMutasi(
                                               '.$itemTitipan->it_id.',          
                                                \''.date('d-m-Y',strtotime($itemTitipan->it_date)).'\',   
                                                \''.$itemTitipan->it_code.'\',
                                                \''.$itemTitipan->it_keterangan.'\'        
                          )" ><i class="fa fa-edit"></i>
                          </button>
                          <button class="btn btn-sm btn-danger" title="Hapus" onclick="deleteMutasi(
                                               '.$itemTitipan->it_id.',          
                                                \''.date('d-m-Y',strtotime($itemTitipan->it_date)).'\',   
                                                \''.$itemTitipan->it_code.'\',
                                                \''.$itemTitipan->it_keterangan.'\'  
                          )" ><i class="fa fa-times"></i>
                          </button>
                          </div>';
                            return $html;
                        })
                        ->rawColumns(['action'])
                      ->make(true);                                  
    }
    static function dataTitipan($id){      
             $itemTitipan=d_item_titipan::join('m_supplier','s_id','=','it_supplier')->where('it_comp',Session::get('user_comp'))->where('it_id',$id)->first();             
             return $itemTitipan;

    }
     static function store($request){
         return DB::transaction(function () use ($request) {               
          
          
    $it_id=d_item_titipan::max('it_id')+1;



     $query = DB::select(DB::raw("SELECT MAX(RIGHT(it_code,4)) as kode_max from d_item_titipan WHERE DATE_FORMAT(it_created, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')"));
      $kd = "";

      if(count($query)>0)
      {
        foreach($query as $k)
        {
          $tmp = ((int)$k->kode_max)+1;
          $kd = sprintf("%05s", $tmp);
        }
      }
      else
      {
        $kd = "00001";
      }
      

      $it_code = "IT-".date('ym')."-".$kd;
      $it_total= format::format($request->it_total);        
        
      d_item_titipan::create([
            'it_id'=>$it_id,
            'it_supplier'=>$request->id_supplier,
            'it_comp'=>Session::get('user_comp'),
            'it_code'=>$it_code,
            'it_date'=>date('Y-m-d',strtotime($request->it_date)),
            'it_keterangan'=>$request->it_keterangan,
            'it_total' =>$it_total,
        ]);


      $jumlah=count($request->idt_item);      
    for ($i=0; $i <$jumlah ; $i++) { 
      
        $idt_qty= format::format($request->idt_qty[$i]); 
        $hpp= format::format($request->idt_price[$i]);         
        if(mutasi::tambahmutasi($request->idt_item[$i],$idt_qty,1,1,'Barang Titipan',1,$it_id,'','',$hpp)){
            $idt_detailid=d_itemtitipan_dt::where('idt_itemtitipan',$it_id)
                               ->max('idt_detailid')+1;                                           
            d_itemtitipan_dt::create([
                'idt_itemtitipan'=>$it_id,
                'idt_detailid'=>$idt_detailid,
                'idt_date'    => date('Y-m-d',strtotime($request->it_date)),
                'idt_comp'=>Session::get('user_comp'),
                'idt_item'=>$request->idt_item[$i],
                'idt_qty'=>$idt_qty,
                'idt_price'=>$hpp    
            ]);
      }
    }  

          $data=['status'=>'sukses','data'=>'sukses'];
          return json_encode($data);
      });
    }




   
}
	