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

   
}
	