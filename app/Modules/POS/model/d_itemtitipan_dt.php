<?php

namespace App\Modules\POS\model;

use Illuminate\Database\Eloquent\Model;

use DB;

use Datatables;

use App\Lib\format;

use App\Lib\mutasi;

use Session;

class d_itemtitipan_dt extends Model
{  
    protected $table = 'd_itemtitipan_dt';
    protected $primaryKey = 'idt_itemtitipan';
    const CREATED_AT = 'idt_created';
    const UPDATED_AT = 'idt_updated';

    protected $fillable = ['idt_itemtitipan','idt_detailid','idt_date','idt_item','idt_qty','idt_price'];

    static function itemTitipanDt($id){
	   	$titipan_dt=d_itemtitipan_dt::
	   						 select('i_id','i_code','idt_itemtitipan','idt_detailid','idt_date','idt_item','idt_qty','idt_price','i_name','m_satuan.s_name','s_qty',DB::raw(" (select sd_qty from d_sales_dt where sd_item=idt_item and idt_date=sd_date) as terjual"))
	   						->join('m_item','idt_item','=','i_id')
	    				    ->join('m_satuan','s_id','=','i_satuan')
	    				    ->join('d_stock','s_item','=','i_id')
	    				    ->where('idt_itemtitipan',$id)
	    				    ->get();
	    				   /*dd($titipan_dt);*/
		return $titipan_dt;
    }
   
}
	